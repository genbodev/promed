/**
* Окно выбора МО
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* 
*/
Ext6.define('common.SelectLpuWindow', {
	alias: 'widget.swSelectLpuWindowExt6',
	width: 550,	height: 185,
	title: langs('Выбор МО'),
	cls: 'arm-window-new ',
	
	noTaskBarButton: true,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(),
	layout: 'border',
	constrain: true,
	addCodeRefresh: Ext6.emptyFn,
	modal: true,
	setCurrentLpu: function(data) {
		var win = this;
		
		closeWindowsAll();
		
		getCountNewDemand();

		// обновляем настройки (getGlobalOptions() и перезагружаем локальные store)
		Ext6.Ajax.request({
			url: C_OPTIONS_LOAD,
			success: function(result){
				Ext.globalOptions = Ext6.util.JSON.decode(result.responseText);
				// Если при получении глобальных настроек вернулась ошибка, то выводим еще и выходим из Промеда
				if  (Ext.globalOptions && (Ext.globalOptions.success!=undefined) && Ext.globalOptions.success === false) {
					Ext6.Msg.alert('Ошибка при загрузке настроек', 'При загрузке настроек с сервера произошла ошибка.<br/><b>'+((Ext.globalOptions.Error_Msg)?Ext.globalOptions.Error_Msg:'')+'</b><br/>Выйдите из системы и зайдите снова.',
						function () {
							window.onbeforeunload = null;
							window.location = C_LOGOUT;
						});

					return true;
				}
				// Подменяем данные с локального хранилища при перечитывании глобальных настроек
				Ext.setLocalOptions();

				user_menu.items.items[0].setHtml('<p><b>' + 
					'Имя: </b> '+UserName+'<br/><b>' + 
					'E-mail: </b> '+UserEmail+'<br/><b>' + 
					'Описание: </b> '+UserDescr+'<br/><b>' + 
					'МО:</b> '+Ext.globalOptions.globals.lpu_nick+'</p>');

				if ( window['swLpuStructureViewForm'] ) {
					swLpuStructureViewForm.close();
					window['swLpuStructureViewForm'] = null;
				}

				loadGlobalStores({
					callback: function () {
						// Открытие АРМа по умолчанию
						if (getGlobalOptions().se_techinfo) {
							openWindowsByTechInfo();
						} else {
							sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
						}
					}
				});
			},
			failure: function(result){
				Ext6.Msg.alert(langs('Ошибка при загрузке настроек'), langs('При загрузке настроек с сервера произошла ошибка. Выйдите из системы и зайдите снова.'));
			},
			method: 'GET',
			timeout: 120000
		});
	},
	afterLoadSpr: function() {
		var win = this;
		var form = this.MainPanel;
		var LpuField = this.queryById('lpucombo');

		LpuField.getStore().clearFilter();
		// Выбираем первое МО в списке
		if (getGlobalOptions().TOUZLpuArr && getGlobalOptions().TOUZLpuArr.length > 0 && !isSuperAdmin() && !getGlobalOptions().isMinZdrav) {
			this.params = getGlobalOptions().TOUZLpuArr;
		} else if ( !getGlobalOptions().superadmin && !isUserGroup(['medpersview', 'ouzuser', 'OuzSpecMPC', 'ouzadmin', 'ouzspec', 'ouzchief', 'roszdrnadzorview']) && !(getGlobalOptions().isMinZdrav && getGlobalOptions().orgtype == 'touz' && isUserGroup(['ouzspec'])) ) {
			// Фильтруем МО, чтобы отображались только те, идентификаторы которых пришли как параметр
			this.params = (this.params)?this.params:getGlobalOptions().lpu;
		}

		var i, lpu_id;
		
		var mainfilter = Ext6.create('Ext6.util.Filter',
			{
				id: 'mainfilter',
				filterFn: function(record) {
					if ( record.get('Lpu_IsAccess') == 1 ) {
						return false;
					}

					var ret = true;

					if ( win.params ) {
						ret = false;

						for (i = 0; i < win.params.length; i++) {
							if ( win.params[i] == record.get('Lpu_id') ) {
								if ( Ext6.isEmpty(lpu_id) ) {
									lpu_id = record.get('Lpu_id');
								}
								ret = true;
								break;
							}
						}
					}

					return ret;
				}
			}
		);
		
		LpuField.getStore().addFilter(mainfilter);

		// Для непустого this.params lpu_id получили в процессе фильтрации
		if ( !this.params ) {
			// Если входных параметров нет (не пришел в форму список МО), то выбираем текущее МО
			var index = LpuField.getStore().findBy(function(rec) {
				return (rec.get('Lpu_id') == getGlobalOptions().lpu_id);
			});

			if ( index >= 0 ) {
				lpu_id = getGlobalOptions().lpu_id;
			}
		}

		if ( !Ext6.isEmpty(lpu_id) ) {
			LpuField.setValue(lpu_id);
		}

		LpuField.focus(true, 100);

		var record = LpuField.getStore().getById(LpuField.getValue());
		LpuField.fireEvent("select", LpuField, record);
		
		this.queryById('button_save').enable();
	},	
	show: function() {
		var win = this;
		this.callParent(arguments);
				
		if ( arguments[0].params ) {
			this.params = arguments[0].params;
		}
		this.queryById('lpucombo').getStore().load();
		
		Ext6.getCmp('change_workplace_menu').setText('Выбрать рабочее место');
	},
	
	/**
	 * Запрос к серверу после выбора МО
	 */
	submit: function() {
		var win = this;
		var form = this.MainPanel.getForm();
		
		this.queryById('button_save').disable();
		
		if (!form.isValid()) {
			Ext6.Msg.alert(langs('Ошибка заполнения формы'),
					langs('Проверьте правильность заполнения полей формы.'));
			this.queryById('button_save').enable();
			return;
		}
		form.submit({
			success : function(form, action) {
				this.hide();
				win.setCurrentLpu(action.result);
				
				this.queryById('button_save').enable();
			}.createDelegate(this),
			failure : function(form, action) {
				
				if  ((action.result) && (action.result.Error_Code))
					Ext6.Msg.alert("Ошибка", '<b>Ошибка '
									+ action.result.Error_Code
									+ ' :</b><br/> '
									+ action.result.Error_Msg);
				this.queryById('button_save').enable();
			}.createDelegate(this)
		});
	}, //end submit()
	
	initComponent: function() {
		var win = this;

		win.MainPanel = new Ext6.form.FormPanel({
			bodyPadding: '25 25 25 30',
			region: 'center',
			border: false,
			items:[{
				xtype: 'baseCombobox',
				allowBlank: false,
				width: 490,
				itemId: 'lpucombo',
				name : 'Lpu_id',
				displayField: 'Lpu_Nick',
				codeField: 'Lpu_Nick',
				valueField: 'Lpu_id',
				queryMode: 'local',
				anyMatch: true,
				tpl: new Ext6.XTemplate(					
					'<tpl for="."><div class="selectlpu-combo-item x6-boundlist-item">',
					'<div class="selectlpu-combo-nick">{Lpu_Nick}</div>',
					'<div class="selectlpu-combo-address">{Address}</div>',
					'</div></tpl>'
				),
				store: new Ext6.create('Ext6.data.Store', {
					getById: function(id) {
						var indx = this.findBy(function(rec) {if(rec.data.Lpu_id == id) return rec;});
						if(indx>=0) return this.getAt(indx); else return false;
					},
					fields: [
						{name: 'Lpu_id', mapping: 'Lpu_id'},
						{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
						{name: 'Lpu_Name', mapping: 'Lpu_Name'},
						{name: 'Address', mapping: 'Address'}
					],
					autoLoad: false,
					sorters: {
						property: 'Lpu_Nick',
						direction: 'ASC'
					},
					proxy: {
						type: 'ajax',
						actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
						url : '/?c=User&m=getLpuList',
						reader: {
							type: 'json'
						}
					},
					mode: 'local',
					listeners: {
						'load': function() {
							win.afterLoadSpr();
						}
					}
				}),
				enableKeyEvents: true,
				listeners: {
					'keydown': function( combo, e, eOpts ) {
						if(combo.getValue() == combo.getRawValue()) {
							if(isSuperAdmin())
								combo.getStore().removeFilter('mainfilter');
						}
					},					
					'blur': function(combo) {
						if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 ) {
							combo.clearValue();
							win.queryById('lpu_name').setText('');
							win.queryById('lpu_address').setText('');
							
						}
					},
					'select': function(combo, rec, index) {
						var name_el = win.queryById('lpu_name');
						var addr_el = win.queryById('lpu_address');
						
						var name = {text:'&nbsp;'};
						var addr = {text:'&nbsp;'};
						if(rec) {
							if(rec.get('Lpu_Name')) name_el.setText(rec.get('Lpu_Name')); // name.text = rec.get('Lpu_Name');
							if(rec.get('Address')) addr_el.setText(rec.get('Address')); //addr.text = rec.get('UAddress_Address');
						}
					},
					specialkey: function(combo, e, eOpts) {
						if (e.getKey() == e.ENTER) {
							combo.fireEvent("blur", combo);
							win.submit();
						}
					}
				}
			}, {
				layout: 'column',
				border: false,
				items: [{
					xtype: 'label',
					html: '',
					itemId: 'lpu_name',
					userCls: 'selectlpu-name'
				}]
			}, {
				layout: 'column',
				border: false,
				style: 'font-color: #666;',
				items: [{
					xtype: 'label',
					html: '',
					itemId: 'lpu_address',
					userCls: 'selectlpu-address'
				}]
			}],
			url : C_USER_SETCURLPU
		});

		Ext6.apply(win, {
			items: [
				win.MainPanel
			],
			buttons: ['->',
			{
				text: langs('ОТМЕНА'),
				itemId: 'button_cancel',
				userCls:'buttonPoupup buttonCancel',
				handler:function () {
					win.hide();
				}
			},
			{
				text: langs('ПРИМЕНИТЬ'),
				itemId: 'button_save',
				userCls:'buttonPoupup buttonAccept',
				handler: function() {
					this.submit();
				}.createDelegate(this)
			}
			]
		});

		this.callParent(arguments);
	}
});