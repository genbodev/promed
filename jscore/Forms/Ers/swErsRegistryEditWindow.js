/**
* Форма Реестр талонов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swErsRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Реестр талонов',
	modal: true,
	resizable: false,
	maximized: false,
	width: 450,
	height: 220,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',

	doSave: function() {
		var win = this,
			base_form = this.MainPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE }),
			params = {
				ErsRegistry_Date: Ext.util.Format.date(base_form.findField('ErsRegistry_Date').getValue(), 'd.m.Y'),
				ErsRegistry_Number: base_form.findField('ErsRegistry_Number').getValue()
			};
		
		if (!base_form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		loadMask.show();	
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.success) {
						win.hide();
						win.callback();
					}	
				}
				else {
					Ext.Msg.alert('Ошибка', 'При сохранении произошла ошибка');
				}
				
			}
		});
	},

	getNumber: function() {

		if (this.action == 'view') return false;

		var win = this,
			base_form = this.MainPanel.getForm(),
			lm = this.getLoadMask('Получение номера реестра'),
			params = base_form.getValues();

		lm.show();
		Ext.Ajax.request({
			url: '/?c=ErsRegistry&m=getNumber',
			params: params,
			method: 'post',
			callback: function(opt, success, response) {
				lm.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (success && response_obj.ErsRegistry_Number) {
					base_form.findField('ErsRegistry_Number').setValue(response_obj.ErsRegistry_Number);
				}
				else {
					sw.swMsg.alert('Ошибка', 'При получении номера реестра произошла ошибка');
				}
			}
		});
	},
	
	show: function() {
		sw.Promed.swErsRegistryEditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.ErsRegistry_id = arguments[0].ErsRegistry_id || null;
		
		base_form.reset();
		
		switch (this.action){
			case 'add':
				this.setTitle('Реестр талонов: Добавление');
				log(win.buttons[0]);
				win.buttons[0].setText(BTN_FRMSAVE);
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle('Реестр талонов: Редактирование');
				win.buttons[0].setText('Переформировать');
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle('Реестр талонов: Просмотр');
				this.enableEdit(false);
				break;
		}
		
		switch (this.action){
			case 'add':
				base_form.findField('ErsRegistry_Date').setValue(getGlobalOptions().date);
				base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
				this.yearCombo.setValue(String(getGlobalOptions().date).substr(6, 4));		
				this.monthCombo.setValue(Number(String(getGlobalOptions().date).substr(3, 2)));
				this.getNumber();
				this.onLoad();
				break;
			case 'edit':
			case 'view':
				var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
				loadMask.show();
				base_form.load({
					url: '/?c=ErsRegistry&m=load',
					params: {
						ErsRegistry_id: win.ErsRegistry_id
					},
					success: function (form, action) {
						loadMask.hide();
						win.onLoad();
					},
					failure: function (form, action) {
						loadMask.hide();
						if (!action.result.success) {
							Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
							this.hide();
						}
					}
				});
				break;
		}
	},
	
	onLoad: function() {
		
		var win = this,
			base_form = this.MainPanel.getForm();
			
		if (this.action == 'view') return false;
		
		this.filterMonthCombo();
	},	
	
	filterMonthCombo: function() {
		this.monthCombo.lastQuery = '';
		this.monthCombo.getStore().clearFilter();
		// если год текущий, то надо отфильтровать месяцы после текущего
		if ( this.yearCombo.getValue() == String(getGlobalOptions().date).substr(6, 4) ) {
			var cur_month = Number(String(getGlobalOptions().date).substr(3, 2));
			this.monthCombo.getStore().filterBy(function(rec) {
				if ( rec.data.value > cur_month )
					return false;
				return true;
			});
			if ( cur_month < this.monthCombo.getValue() )
				this.monthCombo.setValue(cur_month);
		}
	},
	
	initComponent: function() {
		var win = this;
		
		var month_store = [
			[1, langs('Январь')],
			[2, langs('Февраль')],
			[3, langs('Март')],
			[4, langs('Апрель')],
			[5, langs('Май')],
			[6, langs('Июнь')],
			[7, langs('Июль')],
			[8, langs('Август')],
			[9, langs('Сентябрь')],
			[10, langs('Октябрь')],
			[11, langs('Ноябрь')],
			[12, langs('Декабрь')]
		];
		
		year_store = [];
		
		var cur_year = String(getGlobalOptions().date).substr(6, 4);
		
		for ( var i = cur_year-2; i <= cur_year; i++ )
			year_store.push([i, String(i)]);
		
		year_store.reverse();
		
		this.monthCombo = new Ext.form.ComboBox({
			allowBlank: false,
			fieldLabel: 'Месяц отчетного периода',
			width: 150,
			triggerAction: 'all',
			hiddenName: 'ErsRegistry_Month',
			store: month_store,
			listeners: {
				'change': function() {
					win.getNumber()
				}
			}
		});
		
		this.yearCombo = new Ext.form.ComboBox({
			allowBlank: false,
			fieldLabel: 'Год отчетного периода',
			triggerAction: 'all',
			width: 100,
			store: year_store,
			hiddenName: 'ErsRegistry_Year',
			listeners: {
				'change': function() {
					win.filterMonthCombo();
					win.getNumber();
				}
			}
		});
		
		this.MainPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoheight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: true,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			labelWidth: 220,
			items: [{
				name: 'ErsRegistry_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				xtype: 'swdatefield',
				name: 'ErsRegistry_Date',
				disabled: true,
				width: 100,
				fieldLabel: 'Дата формирования'
			}, 
			this.yearCombo,
			this.monthCombo,
			{
				xtype: 'textfield',
				name: 'ErsRegistry_Number',
				disabled: true,
				width: 150,
				fieldLabel: 'Номер реестра'
			}, {
				xtype: 'textfield',
				name: 'ErsRegistry_TicketsCount',
				regex: /[\d]/i,
				maskRe: /\d/i,
				width: 100,
				fieldLabel: 'Количество талонов в реестре'
			}],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'ErsRegistry_id' },
				{ name: 'Lpu_id' },
				{ name: 'ErsRegistry_Date' },
				{ name: 'ErsRegistry_Month' },
				{ name: 'ErsRegistry_Year' },
				{ name: 'ErsRegistry_Number' },
				{ name: 'ErsRegistry_TicketsCount' }
			]),
			url: '/?c=ErsRegistry&m=save'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			items: [
				this.MainPanel
			],
			buttons: [{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}]
		});
		
		sw.Promed.swErsRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});