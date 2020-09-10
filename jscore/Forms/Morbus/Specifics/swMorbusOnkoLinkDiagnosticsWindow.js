/**
 * swMorbusOnkoLinkDiagnosticsWindow - окно редактирования "Результаты диагностики"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      06.2015
 * @comment
 */



sw.Promed.swMorbusOnkoLinkDiagnosticsWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	formParams: null,
	winTitle: langs('Результаты диагностики'),
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 500,
	height: 250,
	maximizable: true,
	autoScroll: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	doSave:  function() {
		var me = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						me.findById('swMorbusOnkoLLinkDiagnosticsEditForm').getFirstInvalidEl().focus(true);
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
		var me = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var formParams = this.form.getValues();

		formParams.DiagAttribDict_id = this.form.findField('DiagAttribDict_id').getValue();

		Ext.Ajax.request({
			params: formParams,
			method: 'POST',
			success: function (result) {
				loadMask.hide();
				if (result.responseText) {
					var response = Ext.util.JSON.decode(result.responseText);
					me.callback(formParams);
					if(Ext.isEmpty(response.Error_Code))
						me.hide();
				}
			},
			failure: function (result) {
				loadMask.hide();
				if (result.responseText) {
					var err = Ext.util.JSON.decode(result.responseText);
					sw.swMsg.alert(langs('Ошибка'), err);
				}
			},
			url:'/?c=MorbusOnkoSpecifics&m=saveMorbusOnkoLink'
		});
	},
	show: function() {
		var me = this;
		sw.Promed.swMorbusOnkoLinkDiagnosticsWindow.superclass.show.apply(this, arguments);
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { me.hide(); });
			return false;
		}
		this.action = arguments[0].action || 'add';
		this.callback = Ext.emptyFn;
		this.formParams = arguments[0].formParams;
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		this.onHide = Ext.emptyFn;
		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		this.form.reset();
		if ( 'add' == this.action && !arguments[0].formParams.MorbusOnko_id ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не верно указаны входные данные 2'), function() { me.hide(); });
			return false;
		}

		switch (this.action) {
			case 'add':
				this.setTitle(this.winTitle +langs(': Добавление'));
				break;
			case 'edit':
				this.setTitle(this.winTitle +langs(': Редактирование'));
				break;
			case 'view':
				this.setTitle(this.winTitle +langs(': Просмотр'));
				break;
		}

		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
		loadMask.show();

		Ext.Ajax.request({
			failure:function () {
				sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
				loadMask.hide();
				me.hide();
			},
			params:{
				Person_id: arguments[0].formParams.Person_id,
				Diag_id: arguments[0].formParams.Diag_id,
				MorbusOnko_id: arguments[0].formParams.MorbusOnko_id
			},
			method: 'POST',
			success: function (response) {
				loadMask.hide();
				var result = Ext.util.JSON.decode(response.responseText);

				me.extraParams = result[0];

				if(me.action != 'add'){

					Ext.Ajax.request({
						failure:function () {
							sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
							loadMask.hide();
							me.hide();
						},
						params:{
							MorbusOnkoLink_id: me.formParams.MorbusOnkoLink_id
						},
						method: 'POST',
						success: function (response) {
							loadMask.hide();
							var result = Ext.util.JSON.decode(response.responseText);
							if (result[0]) {
								me.form.setValues(result[0]);
							}
							me.setEnableAllowFields();
							me.form.findField('MorbusOnkoVizitPLDop_id').setValue(me.formParams.MorbusOnkoVizitPLDop_id);
							me.form.findField('MorbusOnkoDiagPLStom_id').setValue(me.formParams.MorbusOnkoDiagPLStom_id);
							me.form.findField('MorbusOnkoLeave_id').setValue(me.formParams.MorbusOnkoLeave_id);
						},
						url:'/?c=MorbusOnkoSpecifics&m=loadMorbusOnkoLinkDiagnosticsForm'
					});
				}else{
					me.form.setValues(me.formParams);
					me.setEnableAllowFields();
				}

			},
			url:'/?c=MorbusOnkoSpecifics&m=getDiagnosticsFormParams'
		});


		return true;
	},
	setEnableAllowFields: function(){
		var me = this,
			OnkoDiagConfTypeField = me.form.findField('OnkoDiagConfType_id'),
			DiagResultField = me.form.findField('DiagResult_id'),
			DiagAttribTypeField = me.form.findField('DiagAttribType_id'),
			fieldsArray = ['OnkoDiagConfType_id', 'MorbusOnkoLink_takeDT', 'DiagAttribType_id', 'DiagResult_id', 'DiagAttribDict_id'];

		for(var i = 0; i < fieldsArray.length; i++){
			var field = me.form.findField(fieldsArray[i]),
				visible = true,
				allowBlank = true,
				DiagResultRec = DiagResultField.getSelectedRecordData();

			switch(fieldsArray[i]){
				case 'OnkoDiagConfType_id': {
					allowBlank = false;
					break;
				}
				case 'MorbusOnkoLink_takeDT': {
					allowBlank = getRegionNick() == 'kareliya' || !(OnkoDiagConfTypeField.getValue() == 1 && Ext.isEmpty(me.formParams.HistologicReasonType_id) );

					break;
				}
				case 'DiagAttribType_id': {
					visible = OnkoDiagConfTypeField.getValue() == 1 || (getRegionNick() === 'perm' && OnkoDiagConfTypeField.getValue() == 2);
					allowBlank = !(OnkoDiagConfTypeField.getValue() == 1 && Ext.isEmpty(me.formParams.HistologicReasonType_id) && me.extraParams['DiagAttribType_ids'].length > 0);

					field.clearBaseFilter();
					if(OnkoDiagConfTypeField.getValue() == 1 && me.extraParams['DiagAttribType_ids'].length > 0){
						field.setBaseFilter(function(rec){
							return rec.get('DiagAttribType_id').toString().inlist(me.extraParams['DiagAttribType_ids']);
						})
					}
					//field.store.reload();
					break;
				}
				case 'DiagResult_id': {
					allowBlank = !(OnkoDiagConfTypeField.getValue() == 1 && Ext.isEmpty(me.formParams.HistologicReasonType_id) && me.extraParams['DiagResult_ids'].length > 0);
					visible = OnkoDiagConfTypeField.getValue() == 1 || (getRegionNick() == 'perm' && OnkoDiagConfTypeField.getValue() == 2);

					field.setBaseFilter(function (rec) {
						return (Ext.isEmpty(me.formParams.Evn_disDate) || Ext.isEmpty(rec.get('DiagResult_endDT')) || rec.get('DiagResult_endDT') > Date.parseDate(me.formParams.Evn_disDate, 'd.m.Y'));
					});

					if(OnkoDiagConfTypeField.getValue() == 1 && me.extraParams['DiagResult_ids'].length > 0){
						field.setBaseFilter(function (rec) {
							return (
								rec.get('DiagResult_id').toString().inlist(me.extraParams['DiagResult_ids'])
								&& (
									Ext.isEmpty(me.formParams.Evn_disDate)
									|| Ext.isEmpty(rec.get('DiagResult_endDT'))
									|| rec.get('DiagResult_endDT') > Date.parseDate(me.formParams.Evn_disDate, 'd.m.Y')
								)
								&& (
									Ext.isEmpty(DiagAttribTypeField.getValue())
									|| rec.get('DiagAttribType_id') == DiagAttribTypeField.getValue()
								)
							);
						})
					}else if(DiagAttribTypeField.getValue()){
						field.setBaseFilter(function (rec) {
							return (
								rec.get('DiagAttribType_id') == DiagAttribTypeField.getValue()
								&& (
									Ext.isEmpty(me.formParams.Evn_disDate)
									|| Ext.isEmpty(rec.get('DiagResult_endDT'))
									|| rec.get('DiagResult_endDT') > Date.parseDate(me.formParams.Evn_disDate, 'd.m.Y')
								));
						})
					}
					//field.store.reload();
					break;
				}
				case 'DiagAttribDict_id': {
					if(DiagResultRec)
						field.setValue(DiagResultRec.DiagAttribDict_id);
					visible = OnkoDiagConfTypeField.getValue() == 1 || (getRegionNick() == 'perm' && OnkoDiagConfTypeField.getValue() == 2);
				}
			}

			if(field.getValue() && field.findRecord && typeof field.findRecord == 'function'){
				if(!field.findRecord(field.valueField, field.getValue())){
					field.reset();
				}
			}

			field.setContainerVisible(visible);
			field.setValue(field.getValue());
			field.setAllowBlank(!visible ? true : allowBlank);
			field.validate();
			if(!visible){
				field.reset();
			}

		}

	},
	initComponent: function() {
		var me = this;

		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: false,
			id: 'swMorbusOnkoLLinkDiagnosticsEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [{
				name: 'MorbusOnko_id',
				xtype: 'hidden'
			},{
				name: 'MorbusOnkoVizitPLDop_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoLeave_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoDiagPLStom_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoLink_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				fieldLabel: 'Метод подтверждения диагноза',
				comboSubject: 'OnkoDiagConfType',
				name: 'OnkoDiagConfType_id',
				xtype: 'swcommonsprcombo',
				typeCode: 'int',
				allowBlank: false,
				listeners: {
					'change': function(cb,newVal){
						me.setEnableAllowFields();
						if(getRegionNick() == 'perm' && newVal == 2){
							var DiagAttribTypeField = me.form.findField('DiagAttribType_id');
							var idx = DiagAttribTypeField.getStore().findBy(function(record) {
								return (record.get(DiagAttribTypeField.valueField) == 3);//устанавливается значение «Цитологический признак»
							});
							if (idx > -1) {
								DiagAttribTypeField.setValue(3);
							}

						}
					}
				}
			},{
				xtype: 'datefield',
				anchor: null,
				format: 'd.m.Y',
				maxValue: getGlobalOptions().date,
				fieldLabel: 'Дата взятия материала',
				name: 'MorbusOnkoLink_takeDT'

			}, {
				xtype: 'swcommonsprcombo',
				comboSubject: 'DiagAttribType',
				anchor:'100%',
				editable: true,
				fieldLabel: 'Тип диагностического показателя',
				name: 'DiagAttribType_id',
				listeners: {
					'change': function() {
						me.setEnableAllowFields();
					}
				}
			}, {
				xtype: 'swcommonsprcombo',
				comboSubject: 'DiagResult',
				anchor:'100%',
				editable: true,
				fieldLabel: 'Результат диагностики',
				name: 'DiagResult_id',
				moreFields: [
					{name: 'DiagAttribType_id', mapping: 'DiagAttribType_id'},
					{name: 'DiagAttribDict_id', mapping: 'DiagAttribDict_id'},
					{name: 'DiagResult_endDT', mapping: 'DiagResult_endDT'},
				],
				listeners: {
					'change': function(combo, newValue) {
						me.setEnableAllowFields();
					}
				}
			}, {
				xtype: 'swcommonsprcombo',
				comboSubject: 'DiagAttribDict',
				anchor:'100%',
				fieldLabel: 'Диагностический показатель',
				disabled: true,
				name: 'DiagAttribDict_id'
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusOnko_id'},
				{name: 'Person_id'},
				{name: 'MorbusOnkoLink_id'},
				{name: 'OnkoDiagConfType_id'},
				{name: 'MorbusOnkoLink_takeDT'},
				{name: 'DiagAttribType_id'},
				{name: 'DiagResult_id'},
				{name: 'DiagAttribDict_id'}
			])
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
				[{
					handler: function()
					{
						me.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
					{
						text: '-'
					},
					HelpButton(this),
					{
						handler: function()
						{
							me.hide();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}],
			items:[me.formPanel]
		});
		sw.Promed.swMorbusOnkoLinkDiagnosticsWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.formPanel.getForm();
	}
});