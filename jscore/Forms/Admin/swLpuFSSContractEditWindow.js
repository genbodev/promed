/**
* swLpuFSSContractEditWindow - окно редактирования 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
*/

/*NO PARSE JSON*/
sw.Promed.swLpuFSSContractEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'LpuFSSContractEditWindow',
	layout: 'border',
	maximizable: false,
	width: 650,
	height: 200,
	modal: true,
	codeRefresh: true,
	action: 'add',
	show: function() {		
		sw.Promed.swLpuFSSContractEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('LpuFSSContractEditForm').getForm();
		base_form.reset();

		if (arguments[0]['action']) {
			this.action = arguments[0]['action'];
		}
		
		this.LpuFSSContract_id = arguments[0].LpuFSSContract_id || null;
		this.owner = arguments[0].owner || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		
		switch (this.action){
			case 'add':
				this.setTitle(langs('Договор с ФСС: Добавление'));
				break;
			case 'edit':
				this.setTitle(langs('Договор с ФСС: Редактирование'));
				break;
			case 'view':
				this.setTitle(langs('Договор с ФСС: Просмотр'));
				break;
		}
		
		if (this.action != 'add') {
			var loadMask = new Ext.LoadMask(Ext.get('LpuFSSContractEditForm'), { msg: LOAD_WAIT });
			loadMask.show();
			this.findById('LpuFSSContractEditForm').getForm().load({
				url: '/?c=LpuFSSContract&m=load',
				params: {
					LpuFSSContract_id: this.LpuFSSContract_id
				},
				success: function (form, action) {
					loadMask.hide();
					base_form.findField('LpuFSSContractType_id').focus();
				},
				failure: function (form, action) {
					loadMask.hide();
					if (!action.result.success) {
						Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
						this.hide();
					}
				}
			});		
		} else {
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
		}
		
		if (this.action=='view') {
			this.enableEdit(false);
			this.buttons[0].disable();
		} else {
			this.enableEdit(true);
			this.buttons[0].enable();
		}
		
	},
	
	doSave: function() {
		var win = this,
			form = this.findById('LpuFSSContractEditForm').getForm();
		
		if (!form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('LpuFSSContractEditForm').getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('LpuFSSContractEditForm'), { msg: LOAD_WAIT_SAVE });
		loadMask.show();		
		form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result && action.result.success) {
					var id = action.result.LpuFSSContract_id;
					win.hide();	
					if(win.owner) {
						win.owner.refreshRecords(win.owner, id);
					}
				}
				else {
					Ext.Msg.alert(langs('Ошибка'), langs('При сохранении договора произошла ошибка'));
				}
							
			}.createDelegate(this)
		});
	},

	initComponent: function() {
	
		var win = this;
		
		this.MainPanel = new Ext.form.FormPanel({
			id:'LpuFSSContractEditForm',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 220,
			items:
			[{
				name: 'LpuFSSContract_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				xtype: 'swcommonsprcombo',
				width: 350,
				allowBlank: false,
				listWidth: 550,
				comboSubject: 'LpuFSSContractType',
				fieldLabel: 'Вид услуг по договору'
			}, {
				xtype: 'textfield',
				width: 100,
				allowBlank: false,
				fieldLabel: 'Номер договора',
				name: 'LpuFSSContract_Num',
			}, {
				fieldLabel: 'Дата начала действия договора',
				width: 100,
				allowBlank: false,
				name: 'LpuFSSContract_begDate',
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Дата окончания действия договора',
				width: 100,
				allowBlank: false,
				name: 'LpuFSSContract_endDate',
				xtype: 'swdatefield'
			}],
			reader: new Ext.data.JsonReader({},
			[
				{ name: 'LpuFSSContract_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuFSSContractType_id' },
				{ name: 'LpuFSSContract_Num' },
				{ name: 'LpuFSSContract_begDate' },
				{ name: 'LpuFSSContract_endDate' },
			]
			),
			url: '/?c=LpuFSSContract&m=save'
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel],
			buttons:
			[{
				text: BTN_FRMSAVE,
				iconCls: 'save16',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this)
			}, {
				text:'-'
			}, {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function()
				{
					this.hide();
				}.createDelegate(this)
			}],
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

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.doSave();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swLpuFSSContractEditWindow.superclass.initComponent.apply(this, arguments);
	}
});