/**
* Форма 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swSendTicketsToFssWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Регистрация талонов в ФСС',
	modal: true,
	resizable: false,
	maximized: false,
	width: 1020,
	height: 270,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swSendTicketsToFssWindow',
	closeAction: 'hide',
	
	doSave: function() {
		var win = this,
			base_form = this.MainPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE })
			params = {};
		
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

		getWnd('swERSSignatureWindow').show({
			EMDRegistry_ObjectName: 'Данные Талонов от ' + getGlobalOptions().date,
			isMOSign: true,
			callback: function(data) {
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
								sw.swMsg.show({buttons: sw.swMsg.OK, icon: sw.swMsg.INFO, msg: 'Запрос успешно сформирован и отправлен в ФСС'});
							}	
						}
						else {
							Ext.Msg.alert('Ошибка', 'При сохранении произошла ошибка');
						}
					}
				});
			}
		});
	},
	
	show: function() {
		sw.Promed.swSendTicketsToFssWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
			
		if (!arguments.length) arguments = [{}];
		
		this.callback = arguments[0].callback || Ext.emptyFn;
		
		base_form.reset();
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		base_form.load({
			url: '/?c=EvnErsBirthCertificate&m=loadPersonData',
			params: {
				Lpu_id: getGlobalOptions().lpu_id
			},
			success: function (form, action) {
				base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
				loadMask.hide();
				win.onLoad();
				base_form.findField('LpuFSSContract_id').focus();
			},
			failure: function (form, action) {
				loadMask.hide();
				if (!action.result.success) {
					Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
					this.hide();
				}
			}
		});	
	},
	
	onLoad: function() {
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.LpuPanel.loadLpuFSSContractCombo();
	},
	
	initComponent: function() {
		var win = this;
		
		this.LpuPanel = new sw.Promed.ErsLpuPanel;
		
		this.MainPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoheight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			region: 'center',
			labelAlign: 'right',
			labelWidth: 180,
			items: [ 
			this.LpuPanel,
			{
				xtype: 'swcommonsprcombo',
				comboSubject: 'ERSTicketType',
				fieldLabel: 'Тип талона',
				allowBlank: false,
				showCodefield: false,
				width: 150
			}, {
				xtype: 'textfield',
				width: 150,
				regex: /[\d]/i,
				maskRe: /\d/i,
				name: 'TicketsNumber',
				fieldLabel: 'Количество талонов'
			}],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'Lpu_id' },
				{ name: 'Org_INN' },
				{ name: 'Org_KPP' },
				{ name: 'Org_OGRN' },
			]),
			url: '/?c=EvnErsTicket&m=SendTicketsToFss'
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
				text: 'Отправить в ФСС'
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
		
		sw.Promed.swSendTicketsToFssWindow.superclass.initComponent.apply(this, arguments);
	}
});