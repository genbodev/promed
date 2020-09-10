/**
* swMedServiceMedPersonalEditWindow - окно просмотра, добавления и редактирования врачей служб
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @comment      tabIndex: TABINDEX_MS + (16-30)
*/

/*NO PARSE JSON*/
sw.Promed.swMedServiceMedPersonalEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMedServiceMedPersonalEditWindow',
	objectSrc: '/jscore/Forms/Admin/swMedServiceMedPersonalEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swMedServiceMedPersonalEditWindow',
	width: 600,
	height: 260,
	modal: true,
	plain: true,
	resizable: false,
	lpu_id: 0,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	onDoubleMedPersonal: function(combo) {
		var form = this.formPanel.getForm();
		var combo = form.findField('MedPersonal_id');
		var medservicemedpersonal_id = form.findField('MedServiceMedPersonal_id').getValue();
		var medpersonal_id;
		if(this.owner && medservicemedpersonal_id) {
			var store = this.owner.getGrid().getStore();
			var index = store.findBy(function(rec) { return rec.get('MedServiceMedPersonal_id') == medservicemedpersonal_id; });
			if(index >= 0) {
				medpersonal_id = store.getAt(index).get('MedPersonal_id');
			}
		}
		/*
		sw.swMsg.alert(langs('Сообщение'), langs('Данный сотрудник уже указан на службе'), function() {
			if(medpersonal_id)
				combo.setValue(medpersonal_id);
			else
				combo.clearValue();
			combo.focus(true, 250);
		});
		*/
	},
	filterMedStaffFact: function(callback){
		var win = this;
		var form = this.formPanel.getForm();
		
		var comboMedStaffFact = form.findField('MedStaffFact_id');
		var med_personal_id = form.findField('MedPersonal_id').getValue();
		if(comboMedStaffFact.isVisible()){
			setMedStaffFactGlobalStoreFilter({
				Lpu_id: form.findField('Lpu_id').getValue(),
				medPersonalIdList: [med_personal_id]
			});

			comboMedStaffFact.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
			if(comboMedStaffFact.getValue() && comboMedStaffFact.findRecord('MedStaffFact_id', comboMedStaffFact.getValue())){
				comboMedStaffFact.setValue(comboMedStaffFact.getValue());
			}else{
				comboMedStaffFact.clearValue();
			}
		}
	},
	submit: function() {
		var win = this,
			form = this.formPanel.getForm(),
			params = {};

		if ( !form.isValid() ) {
			sw.swMsg.alert(langs('Ошибка заполнения формы'), langs('Проверьте правильность заполнения полей формы.'));
			return;
		}

		var begDT = form.findField('MedServiceMedPersonal_begDT').getValue();
		var endDT = form.findField('MedServiceMedPersonal_endDT').getValue();
		if(endDT && endDT<begDT){
			sw.swMsg.alert(langs('Сообщение'), langs('Дата окончания не может быть меньше даты начала'));
			return false;
		}

		win.getLoadMask(langs('Подождите, сохраняется запись...')).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
				if(action.result.Error_Code && action.result.Error_Code == 7)
					win.onDoubleMedPersonal();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
				var data = {};
				data.MedServiceMedPersonal_id = action.result.MedServiceMedPersonal_id;
				if(win.owner && win.owner.id == 'MedServiceMedPersonal')
				{
					win.callback(win.owner,action.result.MedServiceMedPersonal_id);
				}
				else
				{
					win.callback(data);
				}
			}
		});
	},
	allowEdit: function(is_allow) {
		var win = this,
			form = this.formPanel.getForm(),
			save_btn = this.buttons[0],
			fields = [
				'MedPersonal_id',
				'MedServiceMedPersonal_begDT',
				'MedServiceMedPersonal_endDT'
			];

		for (var i=0; fields.length>i; i++) {
			form.findField(fields[i]).setDisabled(!is_allow);
		}

		if (is_allow) {
			form.findField('MedPersonal_id').focus(true, 250);
			save_btn.show();
		} else {
			save_btn.hide();
		}
	},

	initComponent: function() {
		var win = this;
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'MedServiceMedPersonalRecordEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			region: 'center',
			items: [
				{
					name: 'Lpu_id',
					id: 'MSEW_Lpu_id',
					xtype: 'hidden'
				},{
				hiddenName: 'MedPersonal_id',
				allowBlank: false,
				tabIndex: TABINDEX_MS + 17,
				// xtype: 'swmedpersonalallcombo',
				xtype: 'swmedpersonalisopenmocombo',
				loadingText: langs('Идет поиск...'),
				minChars: 1,
				minLength: 1,
				minLengthText: langs('Поле должно быть заполнено'),
				fieldLabel: langs('Сотрудник'),
				listeners:
				{
					select: function(combo,record,index)
					{
						var form = this.formPanel.getForm();
						/* проверка на сервере происходит
						var medservicemedpersonal_id = form.findField('MedServiceMedPersonal_id').getValue() || 0;
						if(this.owner) {
							var index = this.owner.getGrid().getStore().findBy( function(r,id){
								if(record.get('MedPersonal_id') == r.get('MedPersonal_id') && medservicemedpersonal_id != r.get('MedServiceMedPersonal_id'))
									return true;
							});
							if(index >= 0) {
								this.onDoubleMedPersonal();
								return false;
							}
						}
						*/
						form.findField('MedServiceMedPersonal_begDT').setValue(record.get('WorkData_begDate'));
						form.findField('MedServiceMedPersonal_endDT').setValue(record.get('WorkData_endDate'));
						this.filterMedStaffFact();
					}.createDelegate(this)
				}
			}, {
				fieldLabel: langs('Дата начала'),
				name: 'MedServiceMedPersonal_begDT',
				allowBlank: false,
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_MS + 22,
				xtype: 'swdatefield'
			}, {
				fieldLabel: langs('Дата окончания'),
				name: 'MedServiceMedPersonal_endDT',
				allowBlank: true,
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_MS + 23,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Запрет на одобрение результатов исследований',
				name: 'MedServiceMedPersonal_isNotApproveRights',
				xtype: 'checkbox'
			}, {
				fieldLabel: 'Запрет на создание заявки без записи',
				name: 'MedServiceMedPersonal_isNotWithoutRegRights',
				xtype: 'checkbox'
			}, {
				fieldLabel: 'Передавать данные в ЕГИСЗ',
				name: 'MedServiceMedPersonal_IsTransfer',
				xtype: 'checkbox'
			}, {
				fieldLabel: 'Место работы',
				name: 'MedStaffFact_id',
				hiddenName: 'MedStaffFact_id',
				listWidth: 650,
				width: 350,
				xtype: 'swmedstafffactglobalcombo'
			},{
				name: 'MedServiceMedPersonal_id',
				xtype: 'hidden'
			}, {
				name: 'MedService_id',
				xtype: 'hidden'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.submit();
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
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'MedServiceMedPersonal_id' },
				{ name: 'Lpu_id' },
				{ name: 'MedService_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'MedServiceMedPersonal_isNotApproveRights' },
				{ name: 'MedServiceMedPersonal_isNotWithoutRegRights' },
				{ name: 'MedServiceMedPersonal_begDT' },
				{ name: 'MedServiceMedPersonal_endDT' },
				{ name: 'MedStaffFact_id' },
				{ name: 'MedServiceMedPersonal_IsTransfer' }

			]),
			timeout: 600,
			url: '/?c=MedService&m=saveMedServiceMedPersonalRecord'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_MS + 29,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					this.ownerCt.hide();
				},
				tabIndex: TABINDEX_MS + 30,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swMedServiceMedPersonalEditWindow.superclass.initComponent.apply(this, arguments);
	},

	loadForm: function(){

		var win = this,
			form = win.formPanel.getForm();

		win.formPanel.load({

			url: '/?c=MedService&m=loadMedServiceMedPersonalEditForm',
			params: {
				MedServiceMedPersonal_id: form.findField('MedServiceMedPersonal_id').getValue()
			},
			failure: function() {

				win.getLoadMask().hide();

				sw.swMsg.alert(

					langs('Ошибка'),
					langs('Не удалось загрузить данные с сервера'),
					function() {
						win.hide();
					}
				);
			},
			success: function() {

				win.getLoadMask().hide();

				win.syncSize();
				win.doLayout();
				win.filterMedStaffFact();
			}
		});

	},

	show: function() {

		sw.Promed.swMedServiceMedPersonalEditWindow.superclass.show.apply(this, arguments);

		if (!arguments[0])
			arguments = [{}];

		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;

		// вьюфрейм с врачами служб
		this.owner = arguments[0].owner || null;

		if(!arguments[0].Lpu_id) {
			arguments[0].Lpu_id = getGlobalOptions().lpu_id;
		}

		this.doReset();
		this.center();

		var win = this,
			form = this.formPanel.getForm(),
			mp_combo = form.findField('MedPersonal_id');
	
		var LpuStructureFrame= Ext.getCmp('lpu-structure-frame');
		var selNode = LpuStructureFrame.getSelectionModel().selNode;
	
		var mp_isNotApproveRights = form.findField('MedServiceMedPersonal_isNotApproveRights');
		var mp_isNotWithoutRegRights = form.findField('MedServiceMedPersonal_isNotWithoutRegRights');

		var mp_IsTransfer = form.findField('MedServiceMedPersonal_IsTransfer');
		var comboMedStaffFact = form.findField('MedStaffFact_id');
		comboMedStaffFact.getStore().removeAll();

		if (!selNode.attributes.MedServiceType_SysNick.inlist(['lab','pzm','reglab'])) {
			mp_isNotApproveRights.hideContainer();
			mp_isNotWithoutRegRights.hideContainer();
		} else {
			mp_isNotApproveRights.showContainer();
			mp_isNotWithoutRegRights.showContainer();
		}
		if (!selNode.attributes.MedServiceType_SysNick.inlist(['profosmotrvz','profosmotr'])) {
			mp_IsTransfer.hideContainer();
			comboMedStaffFact.hideContainer();
		} else {
			mp_IsTransfer.showContainer();
			comboMedStaffFact.showContainer();
		}
		win.setHeight(70 + this.formPanel.getSize().height);
		
		form.setValues(arguments[0]);

		mp_combo.getStore().removeAll();
		mp_combo.getStore().load(
			{
				params: {
					Lpu_id: arguments[0].Lpu_id
				},
				callback: function(r,o,s)
				{
					mp_combo.setValue(mp_combo.getValue());
				}
			});

		win.getLoadMask(langs('Пожалуйста, подождите, идет загрузка данных формы...')).show();

		switch (this.action) {

			case 'add':
				this.setTitle(langs('Сотрудник на службе') + ': ' + langs('Добавление'));
				win.allowEdit(true);
				win.getLoadMask().hide();
				break;

			case 'edit':

				this.setTitle(langs('Сотрудник на службе') + ': ' + langs('Редактирование'));

				win.allowEdit(true);
				win.loadForm();

			break;

			case 'view':

				this.setTitle(langs('Сотрудник на службе') + ': ' + langs('Просмотр'));
				win.allowEdit(false);
				win.loadForm();

			break;

			default:
				log('swMedServiceMedPersonalEditWindow - action invalid');
				return false;
			break;
		}
	}
});
