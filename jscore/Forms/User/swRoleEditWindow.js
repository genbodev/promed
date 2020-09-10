/**
* swRoleEditWindow - окно просмотра и редактирования прав группы.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Марков Андрей
* @version      декабрь.2011
*
*/

sw.Promed.swRoleEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	closable: true,
	maximized: true,
	shim: false,
	maximizable: true,
	closeAction: 'hide',
	id: 'swRoleEditWindow',
	title: 'Права',
	// iconCls: 'roles16',
	plain: true,
	callback: Ext.emptyFn,
	buttons: [
		'-'/*,
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}*/, {
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : 'Закрыть',
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	show: function() 
	{
		sw.Promed.swRoleEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0]) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+this.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		this.Role_id = (arguments[0].id)?arguments[0].id:null;
		this.Role_name = (arguments[0].name)?arguments[0].name:'';
		this.Role_type = (arguments[0].type)?arguments[0].type:'';
		if (!this.initTitle) {
			this.initTitle = this.title;
		}
		this.setTitle(this.initTitle+((this.Role_type=='Group')?lang['gruppyi']:lang['cheloveka']) + '"' + this.Role_name+'"');
		this.onTabSelect();
	},
	/** Доступ к редактированию/удалению в зависимости от типа группы 
	 * 
	 */ 
	isUserAccess: function(type) {
		return ((type>=0 && isSuperAdmin()) || (type>=1 && isLpuAdmin()) || (type==2));
	},
	
	onSelect: function() {
		var record = this.ObjectListPanel.getGrid().getSelectionModel().getSelected();
		if (record) {
			this.hide();
			this.callback(record.data);
		}
	},

	onTabSelect: function () {
		var tab = this.DataTab;
		
		switch  (tab.getActiveTab().id) {
			case 'menus': case 'windows':
				// Показать грид 
				
				// запрос для грида 
				Ext.Ajax.request({
					url: '/?c=User&m=getObjectHeaderList',
					params: {
						node: tab.getActiveTab().id
					},
					callback: function(options, success, response) {
						if (success) {
							this.ObjectListPanel.removeAll();
							var result = Ext.util.JSON.decode(response.responseText);
							// Делаем гриду колонки 
							// Получили колоночную модель
							if (result[0]) {
								var cm = this.ObjectListPanel.getColumnModel();
								// Получили количество колонок 
								// Скрываем ненужные и показываем нужные колонки 
								this.columns = [];
								for (i=4; i < cm.getColumnCount(); i++) {
									// Если в пришедшем ответе есть такое же поле как в гриде, то мы его откроем 
									/*
									console.log(cm.getDataIndex(i));
									console.log(result[0]);
									console.log(cm.getDataIndex(i));
									console.log(result[0][cm.getDataIndex(i)]);
									*/
									if (result[0][cm.getDataIndex(i)]) {
										cm.setHidden(i, false);
										this.columns.push(cm.getDataIndex(i));
									} else {
										cm.setHidden(i, true);
									}
									//console.log(cm.getColumnId(i));
								}
							} else {
								// вообще не пришло никакого хидера 
							}
							/*
							for(item in result[0]) {
								this.ObjectListPanel.setColumnHidden(item, false);
							}
							*/
							this.reloadGrid(tab);
						}
					}.createDelegate(this)
				});
				break;
		}
		this.syncSize();
		this.doLayout();
	},
	reloadGrid: function(tab, reload) {
		var group_id = this.ObjectListPanel.getParam('group_id');
		// Перезагружаем только при изменении ветки, чтобы лишний раз не дергать сервер 
		if (tab && (!group_id || group_id != tab.getActiveTab().id || reload)) {
			this.ObjectListPanel.loadData({
				params:{
					node: tab.getActiveTab().id,
					Role_id: this.Role_id
				}, 
				globalFilters:{
					node: tab.getActiveTab().id,
					Role_id: this.Role_id
				}
			});
		}
	},
	
	initComponent: function() {
		this.TreeActions = [];

		this.ContextMenu = new Ext.menu.Menu();

		this.ObjectListPanelMenu = new Ext.menu.Menu({
			id:'ObjectListPanelMenu', 
			items: [{
				text: lang['razreshit_vse'],
				icon: 'img/icons/UploaderIcons/check.gif',
				tooltip: lang['prostavit_razresheniya_vsem_punktam'],
				handler: function() {
					this.ObjectListPanel.checkAll();
				}.createDelegate(this)
			}, {
				text: lang['tolko_prosmotr'],
				icon: 'img/icons/UploaderIcons/check.gif',
				tooltip: lang['prostavit_razresheniya_vsem_punktam'],
				handler: function() {
					this.ObjectListPanel.checkOnlyView();
				}.createDelegate(this)
			}, {
				text: lang['zapretit_vse'],
				icon: 'img/icons/UploaderIcons/uncheck.gif',
				tooltip: lang['snyat_razresheniya_so_vseh_punktov'],
				handler: function() {
					this.ObjectListPanel.uncheckAll();
				}.createDelegate(this)
			}]
		});
		
		this.ObjectListPanel = new sw.Promed.ViewFrame({
			object: 'ObjectList',
			title: lang['obyektyi'],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			//editformclassname: 'swGroupEditWindow',
			region: 'center',
			layout: 'fit',
			pageSize: 20,
			grouping: true,
			toolbar: true,
			saveAtOnce: false, 
			saveAllParams: true, 
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', icon: 'img/icons/actions16.png', text: lang['deystviya'], menu: this.ObjectListPanelMenu},
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print', disabled: true, hidden: true },
				{ name: 'action_save', url: '/?c=User&m=saveObjectRole' }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{name: 'id', type: 'string', hidden: true, key: true },
				{name: 'code', type: 'string', header: lang['kod'], width: 200 },
				{name: 'name', type: 'string', header: lang['nazvanie_obyekta'], id: 'autoexpand'},
				{name: 'group', type: 'string', group: true, sort: true, direction: 'ASC', header: lang['gruppa'], hidden: true, width: 200},
				{name: 'region', type: 'string', hidden: true },
				{name: 'access', type: 'checkcolumnedit', header: lang['dostup'], isparams: true, width: 90, hideable : false },
				{name: 'view', type: 'checkcolumnedit', header: lang['prosmotr'], isparams: true, width: 90, hideable : false },
				{name: 'add', type: 'checkcolumnedit', header: lang['dobavlenie'], isparams: true, width: 90, hideable : false },
				{name: 'edit', type: 'checkcolumnedit', header: lang['izmenenie'], isparams: true, width: 90, hideable : false },
				{name: 'delete', type: 'checkcolumnedit', header: lang['udalenie'], isparams: true, width: 90, hideable : false },
				{name: 'export', type: 'checkcolumnedit', header: lang['eksport'], isparams: true, width: 90, hideable : false },
				{name: 'import', type: 'checkcolumnedit', header: lang['import'], isparams: true, width: 90, hideable : false }				
			],
			// Проверка зависимости, какой именно чекбокс не выбран
			checkAccess: function() {
				
			},
			onAfterEdit: function(o) {
				if (o.record && o.field) {
					 if (o.field == 'view') {
						if (o.value === false) {
							// все поля в цикле проставляем в пусто
							for (i=0; i < this.columns.length; i++) {
								if (o.record.get(this.columns[i]) !== 'hidden') {
									o.record.set(this.columns[i], false);
								}
							}
						}
					} else {
						if (o.value === true && o.record.get('view')===false) {
							o.record.set('view', true);
						}
					}
				}
			}.createDelegate(this),
			/** Поменять разрешения по всем пунктам
			 */
			check: function(enable) {
				var form = this;
				this.ObjectListPanel.getGrid().getStore().each(function(rec) {
					rec.beginEdit();
					for (i=0; i < form.columns.length; i++) {
						if (rec.get(form.columns[i]) !== 'hidden') {
							rec.set(form.columns[i], enable);
						}
					}
					rec.endEdit();
				});
				this.ObjectListPanel.getAction('action_save').setDisabled(false);
			}.createDelegate(this),
			/** Поменять разрешения по всем пунктам (только просмотр)
			 */
			checkOnlyView: function() {
				var form = this;
				this.ObjectListPanel.getGrid().getStore().each(function(rec) {
					rec.beginEdit();
					for (i=0; i < form.columns.length; i++) {
						if (rec.get(form.columns[i]) !== 'hidden') {
							rec.set(form.columns[i], (form.columns[i] == 'view'));
						}
					}
					rec.endEdit();
				});
				this.ObjectListPanel.getAction('action_save').setDisabled(false);
			}.createDelegate(this),
			/** Проставить разрешения всем пунктам
			 */
			checkAll: function() {
				this.check(true);
			},
			/** Снять разрешения со всех пунктов
			 */
			uncheckAll: function(enable) {
				this.check(false);
			},
			// Перекрываем стандартный метод сохранения 
			saveRecord: function (o) {
				var viewframe = this.ObjectListPanel;
				var grid = viewframe.getGrid();
				if (!grid) {
					Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_oshibka_vyibora_grida']);
					return false;
				}
				if ((!viewframe.ViewActions.action_save.initialConfig.url) || (viewframe.ViewActions.action_save.initialConfig.url=='')) {
					Ext.Msg.alert(lang['oshibka'], lang['sistemnaya_oshibka_ne_ukazan_url_dlya_sohraneniya']);
					return false;
				}
				var params = new Object();
				
				// Сохранение отложенно всех записей
				params['object'] = viewframe.object; // Объект
				params['Role_id'] = this.Role_id;
				params['node'] = this.DataTab.getActiveTab().id;
				params['data'] = new Array();
				var k = -1;
				grid.getStore().each(function(record) {
					if (record.get(viewframe.jsonData['key_id'])) {
						/*if (record.dirty) */
						{
							k++;
							params['data'][k] = new Object();
							params['data'][k][viewframe.jsonData['key_id']] = record.get(viewframe.jsonData['key_id']); // Значение ID объекта
							for (i=0; i < this.columns.length; i++) {
								params['data'][k][this.columns[i]] = (record.get(this.columns[i])=='')?false:record.get(this.columns[i]);
							}
						}
					}
				}.createDelegate(this));
				params['data'] = Ext.util.JSON.encode(params['data']);
				viewframe.ViewActions.action_save.setDisabled(true);
				
				// Сам запрос
				Ext.Ajax.request({
					url: viewframe.ViewActions.action_save.initialConfig.url,
					params: params,
					failure: function(response, options) {
						//Ext.Msg.alert('Ошибка', 'При сохранении произошла ошибка!');
					},
					success: function(response, action) {
						if (response.responseText) {
							var answer = Ext.util.JSON.decode(response.responseText);
							if (answer.success) {
								grid.getStore().each(function(record) {
									// 1) это ограничение только для уже существующих строк
									// 2) если сохранять и новые строки то здесь должен быть возврат ID-шников
									if (record.get(viewframe.jsonData['key_id'])) {
										if (record.dirty) {
											record.commit();
										}
									}
								});
							} /*else {
								if (answer.Error_Code) {
									Ext.Msg.alert(lang['oshibka_#']+answer.Error_Code, answer.Error_Message);
								}
							}*/
						}
						else {
							Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_sohranenii_otsutstvuet_otvet_servera']);
						}
					}
				});
				//viewframe.focus();
			}.createDelegate(this),
			onRowSelect: function(sm,index,record) {
				if (this.ObjectListPanel.getCount()>0) {
					this.ObjectListPanel.setActionDisabled('action_delete', (record.get('Group_UserCount')>0));
				}
			}.createDelegate(this),
			dataUrl: '/?c=User&m=getObjectRoleList',
			onLoadData: function() {
			}.createDelegate(this)
		});
		
		this.DataTab = new Ext.TabPanel(
		{
			//resizeTabs:true,
			border: false,
			region: 'north',
			activeTab:0,
			//minTabWidth: 140,
			enableTabScroll: true,
			autoScroll: true,
			defaults: {bodyStyle:'width:100%;'},
			layoutOnTabChange: true,
			listeners:
			{
				tabchange: function(tab, panel)
				{
					this.onTabSelect();
				}.createDelegate(this)
			},
			items:
			[
				{
					title: lang['1_okna'],
					id: 'windows',
					layout: 'fit',
					border:false
				},
				{
					title: lang['2_menyu'],
					disabled: true, // отключил, пока система прав для меню не используется
					id: 'menus',
					layout: 'fit',
					border:false
				}
			]
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			split: true,
			items: [
				this.DataTab,
				this.ObjectListPanel
			]
		});
		sw.Promed.swRoleEditWindow.superclass.initComponent.apply(this, arguments);
	}
});