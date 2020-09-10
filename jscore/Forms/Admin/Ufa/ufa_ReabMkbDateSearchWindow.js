/* 
 * ufa_ReabMkbDateSearchWindow - окно выбора даты и диагноза травмы регистра Реабилитации
 *(контроль диапазона)
 * 
 */

sw.Promed.ufa_ReabMkbDateSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	title: lang['diagnoz_mkb-10'],
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formStatus: 'edit',
	id: 'ReabMkbDateSearch',
	layout: 'form',
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	width: 650,
	filter: '', // для ограничения выбора -- пока через контроль
	initComponent: function ()
	{
		var _this = this;
		//Информационная панель
		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			region: 'north',
			id: 'ReabAnketInformPanel'
		});

		//Панель заполнения и поиска
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			buttonAlign: 'left',
			frame: false,
			id: 'ReabMkbDateIn',
			labelAlign: 'right',
			labelWidth: 120,
			items: [
				{
					xtype: 'combo',
					fieldLabel: 'Врач',
					hiddenName: 'MedPersonal_iid',
					labelAlign: 'left',
					disabled: true,
					name: 'MedPersonal',
					id: 'FIOMedPerson',
					mode: 'local',
					width: 320,
					triggerAction: 'all',
					store: new Ext.data.SimpleStore({
						fields: [{name: 'FIOMedPersonal', type: 'string'}, {name: 'MedPersonal_id', type: 'int'}],
						data: [
							[getGlobalOptions().CurMedPersonal_FIO, 1]
						]
					}),
					displayField: 'FIOMedPersonal',
					valueField: 'MedPersonal_id',
					tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'{quest} ' + '&nbsp;' +
							'</div></tpl>'
				},
				{
					layout: 'form',
					id: 'ReabTravm_setDate',
					border: false,
					items: [
						{
							allowBlank: true,
							disabled: false,
							fieldLabel: lang['data_postanovki_diagnoza'],
							format: 'd.m.Y',
							name: 'ReabTravm_setDate',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							tabIndex: TABINDEX_EDPLEF + 2,
							width: 100,
							xtype: 'swdatefield',
							maxValue: getGlobalOptions().date,
						}
					]
				},
				{
					allowBlank: false,
					hiddenName: 'Diag_id',
					id: 'ReabAnketDiag',
					listWidth: 580,
					tabIndex: TABINDEX_EDPLEF + 5,
					width: 480,
					xtype: 'swdiagcombo'
				},
				{
					layout: 'form',
					border: false,
					id: 'ReabStageComp',
					//labelWidth: 70,
					// labelAlign: 'left',
					items: [
						{
							allowBlank: false,
							anyparam: 'anyparam',
							id: 'ReabStageCompItem',
							labelAlign: 'left',
							listWidth: 'auto',
							emptyText: 'Введите стадию компенсации',
							fieldLabel: lang['stadiya'] + ' ' + lang['diagnoza'],
							hideLabel: false,
							mode: 'local',
							store: new Ext.data.JsonStore(
									{
										url: '?c=ufa_Reab_Register_User&m=ReabSpr',
										autoLoad: false,
										fields:
												[
													{name: 'ReabSpr_Elem_id', type: 'int'},
													{name: 'ReabSpr_Elem_Name', type: 'string'},
													{name: 'ReabSpr_Elem_Weight', type: 'string'}
												],
										key: 'ReabSpr_Elem_id',
									}),
							editable: false,
							triggerAction: 'all',
							displayField: 'ReabSpr_Elem_Name',
							valueField: 'ReabSpr_Elem_id',
							width: 320,
							hiddenName: 'ReabSpr_Elem_id',
							autoscroll: false,
							xtype: 'combo',
							listeners: {
								specialkey: function (field, e) {
									//console.log('FIELD', field)
									if (e.getKey() == e.ENTER) {
										// Ext.getCmp('getMorbusType_id').handler();
									}
								}
							}
						}
					]
				},
			],
			keys: [{
					alt: true,
					fn: function (inp, e) {
						switch (e.getKey()) {
							case Ext.EventObject.C:
								if (this.action != 'view') {
									this.doSave();
								}
								break;

							case Ext.EventObject.J:
								this.hide();
								break;
						}
					},
					key: [Ext.EventObject.C, Ext.EventObject.J],
					scope: this,
					stopEvent: true
				}],
			
			reader: new Ext.data.JsonReader({
					success: function () { }
				}, 
				[
					{name: 'accessType'},
					{name: 'EvnDiagPL_id'}
				]),
			
			url: '/?c=EvnPL&m=saveEvnDiagPL'
		});
		
		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					tabIndex: TABINDEX_EDPLEF + 7,
					text: BTN_FRMSAVE
				}, {
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function () {
						//alert('Отмена');
						Ext.getCmp('ReabStageCompItem').reset();
						Ext.getCmp('ReabStageCompItem').setValue('Введите стадию компенсации');
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						// alert('Отмена');
					}.createDelegate(this),
					onTabAction: function () {
						if (this.action != 'view') {
							this.FormPanel.getForm().findField('EvnVizitPL_id').focus(true, 100);
						}
					}.createDelegate(this),
					tabIndex: TABINDEX_EDPLEF + 8,
					text: BTN_FRMCANCEL
				}
				],
			items: [
					this.PersonInfo,
					this.FormPanel
				]
		});

		sw.Promed.ufa_ReabMkbDateSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'hide': function () {
			if (this.refresh)
				this.onHide();
		},
		'close': function () {
			if (this.refresh)
				this.onHide();
		}
	},

	refresh: function () {
		//  alert('Обновление');
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		//this.close();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];
	},
	show: function (params)
	{
		//console.log('params=', params);
		this.PersonInfo.load({
			Person_id: params.Inparams.Person_id
		});
		//Подсунем врача
		Ext.getCmp('FIOMedPerson').setValue(1);
		//Установим дату
		this.FormPanel.getForm().findField('ReabTravm_setDate').setValue(getGlobalOptions().date);
		Ext.getCmp('ReabAnketDiag').reset();
		//Переопределение метода
		if (arguments[0].callback1)
			this.callback1 = arguments[0].callback1;
		else
			this.callback1 = Ext.emptyFn;
		//Установка окна даты
		if (params.Inparams.InDate && params.Inparams.InDate == 1)
		{

			Ext.getCmp('ReabTravm_setDate').show();
		} else
		{
			Ext.getCmp('ReabTravm_setDate').hide();
		}
		//Установка окна стадии компенсации
		if (params.Inparams.InStage && params.Inparams.InStage == 1)
		{

			Ext.getCmp('ReabStageComp').show();
			Ext.getCmp('ReabStageCompItem').getStore().load(
					{params: {
							SprNumber: 25,
							SprNumberGroup: 1
						}});
		} else
		{
			Ext.getCmp('ReabStageComp').hide();
		}

		//Фильтр для контроля

		if (params.Inparams.Filter)
		{
			this.filter = params.Inparams.Filter;
		} else {
			this.filter = ''
		}
		//console.log('Фильтр=',this.filter); 
		sw.Promed.ufa_ReabMkbDateSearchWindow.superclass.show.apply(this, arguments);
	},
	callback1: function (data) {

	},
	doSave: function () {
		//  alert('Сохраняем');

		//Контроль даты постановки на учет
		if (Ext.getCmp('ReabMkbDateIn').getForm().findField('ReabTravm_setDate').getValue() == '')
		{
			sw.swMsg.show(
					{icon: Ext.MessageBox.ERROR,
						title: lang['oshibka'],
						msg: lang['data'],
						buttons: Ext.Msg.OK
					});
			return false;
		}
		if (!Ext.getCmp('ReabMkbDateIn').getForm().findField('ReabTravm_setDate').isValid())
		{
			sw.swMsg.show(
					{icon: Ext.MessageBox.ERROR,
						title: lang['oshibka'],
						msg: lang['Date_Insert_Reab'],
						buttons: Ext.Msg.OK
					});
			return false;
		}

		//  Контроль уточнения заболевания
		if (Ext.getCmp('ReabStageComp').hidden == false)
		{
			//Контроль стадии
			if (Ext.getCmp('ReabStageCompItem').selectedIndex == -1)
			{
				sw.swMsg.show(
						{icon: Ext.MessageBox.ERROR,
							title: lang['oshibka'],
							msg: 'Уточните стадию диагноза',
							buttons: Ext.Msg.OK
						});
				return false;
			} 
			else
			{
				var ReabStageCompItemName = Ext.getCmp('ReabStageCompItem').getStore().data.items[Ext.getCmp('ReabStageCompItem').selectedIndex].data.ReabSpr_Elem_Name;
				var ReabStageCompItemId = Ext.getCmp('ReabStageCompItem').getStore().data.items[Ext.getCmp('ReabStageCompItem').selectedIndex].data.ReabSpr_Elem_id;
				var ReabStageCompItemWeigth = Ext.getCmp('ReabStageCompItem').getStore().data.items[Ext.getCmp('ReabStageCompItem').selectedIndex].data.ReabSpr_Elem_Weight;
				Ext.getCmp('ReabStageCompItem').selectedIndex = -1;
				Ext.getCmp('ReabStageCompItem').reset();
				Ext.getCmp('ReabStageCompItem').setValue('Введите стадию компенсации');
			}
		} 
		else 
		{
			//Нет контроля
			var ReabStageCompItemName = null;
			var ReabStageCompItemId = null;
			var ReabStageCompItemWeigth = null;
		};

		var cText = Ext.getCmp('ReabAnketDiag').lastSelectionText;
		if (Ext.getCmp('ReabMkbDateIn').getForm().findField('Diag_id').getValue() > 0 && cText.length > 0)
		{
			//валидация
			var yy = cText.trim().indexOf(' ');
			// console.log('yy=',yy); 
			var i1 = cText.trim().substr(0, yy);
			var i2 = cText.replace(i1, '');
			//console.log('Фильтр2222=', this.filter);
			if (this.filter != '')
			{
				//Контроль на выбор
				if (i1.trim().substr(0, 1) != 'S' && i1.trim().substr(0, 1) != 'T')
				{
					sw.swMsg.show(
							{icon: Ext.MessageBox.ERROR,
								title: lang['oshibka'],
								msg: lang['nesovpadenie_diagnoza'],
								buttons: Ext.Msg.OK
							});
					Ext.getCmp('ReabAnketDiag').reset();
					return false;
				}
			}

			//На выход
			var data = new Object();
			data = [{
					'Diag_id': Ext.getCmp('ReabMkbDateIn').getForm().findField('Diag_id').getValue(),
					'Diag_Code': i1,
					'Diag_Name': i2,
					'Travm_setDate': Ext.getCmp('ReabMkbDateIn').getForm().findField('ReabTravm_setDate').getValue(),
					'ReabSpr_Elem_Name': ReabStageCompItemName,
					'ReabSpr_Elem_id': ReabStageCompItemId,
					'ReabSpr_Elem_Weight': ReabStageCompItemWeigth
				}];
			this.callback1(data);
			
		} 
		else 
		{
			sw.swMsg.show(
					{icon: Ext.MessageBox.ERROR,
						title: lang['oshibka'],
						msg: lang['dobavit_diagnoz'],
						buttons: Ext.Msg.OK
					});
			return false;
		}
		this.hide();
		return;
	}
})