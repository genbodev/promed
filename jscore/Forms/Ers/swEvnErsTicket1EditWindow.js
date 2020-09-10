/**
* Форма Талон 1
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swEvnErsTicket1EditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Талон 1',
	modal: true,
	resizable: false,
	maximized: false,
	width: 1020,
	height: 470,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',

	doSave: function() {
		var win = this,
			base_form = this.MainPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		
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
	
	show: function() {
		sw.Promed.swEvnErsTicket1EditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.Person_id = arguments[0].Person_id || null;
		this.EvnERSTicket_id = arguments[0].EvnERSTicket_id || null;
		this.EvnERSTicket_pid = arguments[0].EvnERSTicket_pid || null;
		this.EvnERSBirthCertificate_Number = arguments[0].EvnERSBirthCertificate_Number || null;
		
		base_form.reset();
		
		switch (this.action){
			case 'add':
				this.setTitle('Талон 1: Добавление');
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle('Талон 1: Редактирование');
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle('Талон 1: Просмотр');
				this.enableEdit(false);
				break;
		}
		
		switch (this.action){
			case 'add':
				var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
				loadMask.show();
				base_form.load({
					url: '/?c=EvnErsBirthCertificate&m=loadPersonData',
					params: {
						Lpu_id: getGlobalOptions().lpu_id,
						Person_id: this.Person_id
					},
					success: function (form, action) {
						base_form.findField('EvnERSTicket_pid').setValue(win.EvnERSTicket_pid);
						base_form.findField('EvnERSBirthCertificate_Number').setValue(win.EvnERSBirthCertificate_Number);
						base_form.findField('ERSTicketType_id').setValue(1);
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
			case 'edit':
			case 'view':
				var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
				loadMask.show();
				base_form.load({
					url: '/?c=EvnErsTicket&m=load',
					params: {
						EvnERSTicket_id: win.EvnERSTicket_id
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
		
		this.PersonPanel.checkFields();
	},	
	
	initComponent: function() {
		var win = this;
		
		this.PersonPanel = new sw.Promed.ErsPersonPanel({
			object: 'EvnERSTicket'
		});
		
		this.TicketInfo = new sw.Promed.Panel({
			autoHeight: true,
			bodyStyle: 'padding-top: 0.5em;',
			border: true,
			collapsible: false,
			layout: 'form',
			style: 'margin-bottom: 1em;',
			title: 'Сведения о талоне',
			items: [{
				layout: 'column',
				border: false,
				defaults: {
					border: false,
					style: 'margin-right: 20px;'
				},
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						width: 250,
						disabled: true,
						name: 'EvnERSBirthCertificate_Number',
						fieldLabel: 'Номер ЭРС'
					}, {
						border: false,
						layout: 'form',
						labelWidth: 380,
						items: [{
							xtype: 'numberfield',
							width: 50,
							name: 'EvnErsTicket_PregnancyRegisterTime',
							fieldLabel: 'Срок беременности при постановке на учет (недель)'
						}, {
							xtype: 'numberfield',
							width: 50,
							name: 'EvnErsTicket_PregnancyPutTime',
							fieldLabel: 'Срок беременности на дату формирования Талона (недель)'
						}]
					}, {
						xtype: 'checkbox',
						name: 'EvnErsTicket_IsMultiplePregnancy',
						labelSeparator: '',
						inputValue: '1',
						boxLabel: 'Многоплодная беременность'
					}]
				}, {
					layout: 'form',
					labelWidth: 160,
					items: [{
						xtype: 'numberfield',
						width: 200,
						name: 'EvnErsTicket_StickNumber',
						fieldLabel: 'Номер выданного ЛВН'
					}, {
						xtype: 'numberfield',
						width: 200,
						name: 'EvnErsTicket_CardNumber',
						fieldLabel: 'Номер обменной карты'
					}, {
						xtype: 'swdatefield',
						width: 100,
						name: 'EvnErsTicket_CardDate',
						fieldLabel: 'Дата обменной карты'
					}]
				}]
			}]
		});
		
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
			items: [{
				name: 'EvnERSTicket_id',
				xtype: 'hidden'
			}, {
				name: 'EvnERSTicket_pid',
				xtype: 'hidden'
			}, {
				name: 'ERSTicketType_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			},
			this.PersonPanel, 
			this.TicketInfo
			],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'EvnERSTicket_id' },
				{ name: 'EvnERSTicket_pid' },
				{ name: 'EvnERSBirthCertificate_Number' },
				{ name: 'ERSTicketType_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Person_id' },
				{ name: 'Server_id' },
				{ name: 'Person_SurName' },
				{ name: 'Person_FirName' },
				{ name: 'Person_SecName' },
				{ name: 'Person_BirthDay' },
				{ name: 'Person_Snils' },
				{ name: 'DocumentType_Name' },
				{ name: 'Document_Ser' },
				{ name: 'Document_Num' },
				{ name: 'Document_begDate' },
				{ name: 'OrgDep_Name' },
				{ name: 'Polis_Num' },
				{ name: 'Polis_begDate' },
				{ name: 'Address_Address' },
				{ name: 'Lpu_id' },
				{ name: 'EvnERSTicket_PolisNoReason' },
				{ name: 'EvnERSTicket_SnilsNoReason' },
				{ name: 'EvnERSTicket_DocNoReason' },
				{ name: 'EvnERSTicket_AddressNoReason' },
				{ name: 'EvnErsTicket_PregnancyRegisterTime' },
				{ name: 'EvnErsTicket_PregnancyPutTime' },
				{ name: 'EvnErsTicket_IsMultiplePregnancy' },
				{ name: 'EvnErsTicket_StickNumber' },
				{ name: 'EvnErsTicket_CardNumber' },
				{ name: 'EvnErsTicket_CardDate' }
			]),
			url: '/?c=EvnErsTicket&m=save'
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
		
		sw.Promed.swEvnErsTicket1EditWindow.superclass.initComponent.apply(this, arguments);
	}
});