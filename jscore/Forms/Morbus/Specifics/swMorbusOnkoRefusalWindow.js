/**
* swMorbusOnkoRefusalWindow - окно редактирования "Данные об отказах / противопоказаниях"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MorbusOnko
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @version      03.2019
* @comment      
*/

sw.Promed.swMorbusOnkoRefusalWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'form',
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	maximizable: false,
	modal: true,
	width: 600,
	winTitle: langs('Данные об отказах / противопоказаниях'),

	/* методы */
	callback: Ext.emptyFn,
	doSave:  function() {
		var that = this;

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					that.findById('MorbusOnkoRefusalEditForm').getFirstInvalidEl().focus(true);
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
	filterMorbusOnkoRefusalType: function() {
		var win = this;
		var base_form = this.form;

		var
			MorbusOnkoRefusal_setDT = base_form.findField('MorbusOnkoRefusal_setDT').getValue(),
			MorbusOnkoRefusalType_id = base_form.findField('MorbusOnkoRefusalType_id').getValue();

		base_form.findField('MorbusOnkoRefusalType_id').clearValue();
		base_form.findField('MorbusOnkoRefusalType_id').lastQuery = '';

		base_form.findField('MorbusOnkoRefusalType_id').getStore().clearFilter();
		base_form.findField('MorbusOnkoRefusalType_id').getStore().filterBy(function(rec) {
			return (
				(
					(win.isRefusal == true && rec.get('MorbusOnkoRefusalType_IsRefusal') == 2)
					|| (win.isRefusal == false && rec.get('MorbusOnkoRefusalType_IsRefusal') != 2)
				)
				&& (
					Ext.isEmpty(MorbusOnkoRefusal_setDT)
					|| (
						(!rec.get('MorbusOnkoRefusalType_begDT')  || rec.get('MorbusOnkoRefusalType_begDT') <= MorbusOnkoRefusal_setDT)
						&& (!rec.get('MorbusOnkoRefusalType_endDT') || rec.get('MorbusOnkoRefusalType_endDT') >= MorbusOnkoRefusal_setDT)
					)
				)
			);
		});

		if ( !Ext.isEmpty(MorbusOnkoRefusalType_id) ) {
			var index = base_form.findField('MorbusOnkoRefusalType_id').getStore().findBy(function(rec) {
				return (rec.get('MorbusOnkoRefusalType_id') == MorbusOnkoRefusalType_id);
			});

			if ( index >= 0 ) {
				base_form.findField('MorbusOnkoRefusalType_id').setValue(MorbusOnkoRefusalType_id);
			}
		}
	},
	setFieldsDisabled: function(d) {
		var form = this;

		this.form.items.each(function(f) {
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false)) {
				f.setDisabled(d);
			}
		});

		form.buttons[0].setDisabled(d);
	},
	show: function() {
		sw.Promed.swMorbusOnkoRefusalWindow.superclass.show.apply(this, arguments);

		var that = this;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { that.hide(); });
			return false;
		}

		this.action = arguments[0].action || 'add';
		this.callback = Ext.emptyFn;
		this.isRefusal = false;
		this.onHide = Ext.emptyFn;

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].isRefusal ) {
			this.isRefusal = true;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		this.form.reset();

		if ( 'add' != this.action && !arguments[0].formParams.MorbusOnkoRefusal_id ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не верно указаны входные данные 1'), function() { that.hide(); });
			return false;
		}
		if ( 'add' == this.action && !arguments[0].formParams.MorbusOnko_id ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не верно указаны входные данные 2'), function() { that.hide(); });
			return false;
		}

		switch (this.action) {
			case 'add':
				this.setTitle(this.winTitle + langs(': Добавление'));
				break;
			case 'edit':
				this.setTitle(this.winTitle + langs(': Редактирование'));
				break;
			case 'view':
				this.setTitle(this.winTitle + langs(': Просмотр'));
				break;
		}

		that.form.findField('MorbusOnkoRefusal_setDT').setMaxValue(getGlobalOptions().date);
		
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
		loadMask.show();

		if ('add' == this.action) {
			that.form.setValues(arguments[0].formParams);

			that.filterMorbusOnkoRefusalType();

            loadMask.hide();
		}
		else {
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					loadMask.hide();
					that.hide();
				},
				params:{
					MorbusOnkoRefusal_id: arguments[0].formParams.MorbusOnkoRefusal_id
				},
				method: 'POST',
				success: function (response) {
					loadMask.hide();

					var result = Ext.util.JSON.decode(response.responseText);

					if ( result[0] ) {
						that.form.findField('MorbusOnkoRefusalType_id').getStore().clearFilter();

						that.form.setValues(result[0]);

						that.isRefusal = (that.form.findField('MorbusOnkoRefusalType_id').getFieldValue('MorbusOnkoRefusalType_IsRefusal') == 2);

						that.filterMorbusOnkoRefusalType();
					}
				},
				url:'/?c=MorbusOnkoSpecifics&m=loadMorbusOnkoRefusalEditForm'
			});
		}
		return true;
	},
	submit: function() {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		var formParams = this.form.getValues();

		Ext.Ajax.request({
			params: formParams,
			method: 'POST',
			success: function (result) {
				loadMask.hide();
				if (result.responseText) {
					var response = Ext.util.JSON.decode(result.responseText);
					formParams.MorbusOnkoRefusal_id = response.MorbusOnkoRefusal_id;
					that.callback(formParams);
                    if(Ext.isEmpty(response.Error_Code))
                        that.hide();
				}
			},
			failure: function (result) {
				loadMask.hide();
				if (result.responseText) {
					var err = Ext.util.JSON.decode(result.responseText);
					sw.swMsg.alert(langs('Ошибка'), err);
				}
			},
			url:'/?c=MorbusOnkoSpecifics&m=saveMorbusOnkoRefusalEditForm'
		});
	},

	/* конструктор */
	initComponent: function() {
		var that = this;

		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			frame: false,
			id: 'MorbusOnkoRefusalEditForm',
			bodyStyle: 'background:#DFE8F6;padding:5px;',
			labelWidth: 170,
			labelAlign: 'right',
			items: [{
				name: 'MorbusOnkoRefusal_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnko_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoLeave_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoVizitPLDop_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoDiagPLStom_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: langs('Дата регистрации отказа / противопоказания'),
				listeners: {
					'change': function() {
						that.filterMorbusOnkoRefusalType();
					}
				},
				name: 'MorbusOnkoRefusal_setDT',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				comboSubject: 'MorbusOnkoRefusalType',
				fieldLabel: langs('Тип лечения'),
				hiddenName: 'MorbusOnkoRefusalType_id',
				lastQuery: '',
				listWidth: 500,
				moreFields: [
					{ name: 'MorbusOnkoRefusalType_IsRefusal', type: 'int' },
					{ name: 'MorbusOnkoRefusalType_begDT', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'MorbusOnkoRefusalType_endDT', type: 'date', dateFormat: 'd.m.Y' }
				],
				width: 390,
				xtype: 'swcommonsprlikecombo'
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusOnkoRefusal_id'}, 
				{name: 'MorbusOnko_id'}, 
				{name: 'MorbusOnkoLeave_id'}, 
				{name: 'MorbusOnkoVizitPLDop_id'}, 
				{name: 'MorbusOnkoDiagPLStom_id'}, 
				{name: 'MorbusOnkoRefusal_setDT'}, 
				{name: 'MorbusOnkoRefusalType_id'}
			])
		});

		Ext.apply(this, {
			layout: 'form',
			buttons: [{
				handler: function() {
					that.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					that.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				that.formPanel
			]
		});

		sw.Promed.swMorbusOnkoRefusalWindow.superclass.initComponent.apply(this, arguments);

		this.form = this.formPanel.getForm();
	}
});