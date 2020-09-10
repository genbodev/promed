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
* @version      04.2019
* @comment      
*/

Ext6.define('common.MorbusOnko.swMorbusOnkoRefusalWindow', {
	/* свойства */
	alias: 'widget.swMorbusOnkoRefusalWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'MorbusOnkoRefusaleditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
    winTitle: langs('Данные об отказах / противопоказаниях'),
	title: langs('Данные об отказах / противопоказаниях'),
	width: 800,
	
	save:  function() {
		var win = this;

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('MorbusOnkoRefusalEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		win.mask(LOAD_WAIT_SAVE);

		var formParams = this.form.getValues();

		Ext.Ajax.request({
			params: formParams,
			method: 'POST',
			success: function (result) {
				win.unmask();
				if (result.responseText) {
					var response = Ext.util.JSON.decode(result.responseText);
					formParams.MorbusOnkoRefusal_id = response.MorbusOnkoRefusal_id;
					win.callback(formParams);
                    if(Ext.isEmpty(response.Error_Code))
                        win.hide();
				}
			},
			failure: function (result) {
				win.unmask();
			},
			url:'/?c=MorbusOnkoSpecifics&m=saveMorbusOnkoRefusalEditForm'
		});
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
        this.FormPanel.items.each(function(f){
            if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false)) {
                f.setDisabled(d);
            }
        });
       // form.MorbusOnkoTumorStatusFrame.setReadOnly(d);
        //form.buttons[0].setDisabled(d);
    },
	
	onSprLoad: function(arguments) {
		var win = this;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { win.hide(); });
			return false;
		}

		this.action = arguments[0].action || 'add';
		this.callback = Ext.emptyFn;
		this.isRefusal = false;

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].isRefusal ) {
			this.isRefusal = true;
		}

		this.form.reset();

		if ( 'add' != this.action && !arguments[0].formParams.MorbusOnkoRefusal_id ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не верно указаны входные данные 1'), function() { win.hide(); });
			return false;
		}
		if ( 'add' == this.action && !arguments[0].formParams.MorbusOnko_id ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не верно указаны входные данные 2'), function() { win.hide(); });
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

		win.form.findField('MorbusOnkoRefusal_setDT').setMaxValue(getGlobalOptions().date);
		
		win.mask(LOAD_WAIT);
		win.isLoading = true;

		if ('add' == this.action) {
			win.form.setValues(arguments[0].formParams);

			win.filterMorbusOnkoRefusalType();
			
            win.unmask();
			
			win.isLoading = false;
		}
		else {
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					win.unmask();
					win.hide();
					win.isLoading = false;
				},
				params:{
					MorbusOnkoRefusal_id: arguments[0].formParams.MorbusOnkoRefusal_id
				},
				method: 'POST',
				success: function (response) {
					win.unmask();

					var result = Ext.util.JSON.decode(response.responseText);

					if ( result[0] ) {
						win.form.findField('MorbusOnkoRefusalType_id').getStore().clearFilter();

						win.form.setValues(result[0]);

						win.isRefusal = (win.form.findField('MorbusOnkoRefusalType_id').getFieldValue('MorbusOnkoRefusalType_IsRefusal') == 2);

						win.filterMorbusOnkoRefusalType();
					}
					
					win.isLoading = false;
				},
				url:'/?c=MorbusOnkoSpecifics&m=loadMorbusOnkoRefusalEditForm'
			});
		}
	},

	show: function() {
		this.callParent(arguments);
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		Ext6.define(win.id + '_FormModel', {
			extend: 'Ext6.data.Model'
		});

		win.FormPanel = new Ext6.form.FormPanel({
			border: false,
			cls: 'emk_forms accordion-panel-window subFieldPanel',
			bodyPadding: '15 25 15 37',
			defaults: {
				labelAlign: 'left',
				labelWidth: 200
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
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
						if (!win.isLoading) win.filterMorbusOnkoRefusalType();
					}
				},
				name: 'MorbusOnkoRefusal_setDT',
				xtype: 'datefield'
			}, {
				allowBlank: false,
				comboSubject: 'MorbusOnkoRefusalType',
				fieldLabel: langs('Тип лечения'),
				name: 'MorbusOnkoRefusalType_id',
				listWidth: 500,
				moreFields: [
					{ name: 'MorbusOnkoRefusalType_IsRefusal', type: 'int' },
					{ name: 'MorbusOnkoRefusalType_begDT', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'MorbusOnkoRefusalType_endDT', type: 'date', dateFormat: 'd.m.Y' }
				],
				width: 700,
				xtype: 'commonSprCombo'
			}]
		});

        Ext6.apply(win, {
			items: [
				win.FormPanel
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					win.hide();
				}
			},{
				xtype: 'SubmitButton',
				handler:function () {
					win.save();
				}
			}]
		});

		this.callParent(arguments);
		
		this.form = this.FormPanel.getForm();
    }
});