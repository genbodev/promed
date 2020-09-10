/**
* swUslugaComplexTariffLloEditWindow - окно редактирования тарифа
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      01.2015
* @comment      
*/
sw.Promed.swUslugaComplexTariffLloEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['tarif_llo_redaktirovanie'],
	layout: 'border',
	id: 'UslugaComplexTariffLloEditWindow',
	modal: true,
	shim: false,
	width: 550,
	height: 175,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('UslugaComplexTariffLloEditForm').getFirstInvalidEl().focus(true);
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
		var wnd = this;
		wnd.getLoadMask(lang['podojdite_idet_sohranenie']).show();
		var params = new Object();
		params.UslugaComplexTariff_begDate = wnd.DateRange.getValue1() ? Ext.util.Format.date(wnd.DateRange.getValue1(), 'd.m.Y') : null;
		params.UslugaComplexTariff_endDate = wnd.DateRange.getValue2() ? Ext.util.Format.date(wnd.DateRange.getValue2(), 'd.m.Y') : null;

		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.UslugaComplexTariff_id > 0) {
					var id = action.result.UslugaComplexTariff_id;
					wnd.form.findField('UslugaComplexTariff_id').setValue(id);
					wnd.callback(wnd.owner, id);
					wnd.hide();
				}
			}
		});
	},
	setDisabled: function(disable) {
		var wnd = this;

		var field_arr = [
			'UslugaComplexTariff_id',
			'UslugaComplex_id',
			'UslugaComplexTariff_Tariff'
		];

		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			var combo = wnd.form.findField(field_arr[i]);
			if (disable) {
				combo.disable();
			} else {
				combo.enable();
			}
		}

		if (disable) {
			wnd.DateRange.disable();
			wnd.buttons[0].disable();
		} else {
			wnd.DateRange.enable();
			wnd.buttons[0].enable();
		}
	},
	setDefaultValues: function() {
		var current_date = new Date();

		this.DateRange.setValue(current_date.format('d.m.Y')+' - ');
	},
	show: function() {
        var wnd = this;
		sw.Promed.swUslugaComplexTariffLloEditWindow.superclass.show.apply(this, arguments);

		this.action = '';
		this.callback = Ext.emptyFn;
		this.UslugaComplexTariff_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].UslugaComplexTariff_id ) {
			this.UslugaComplexTariff_id = arguments[0].UslugaComplexTariff_id;
		}

		this.setTitle("Тариф ЛЛО");
		this.form.reset();

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();

		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				this.setDefaultValues();
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						UslugaComplexTariff_id: wnd.UslugaComplexTariff_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0]) {
							wnd.form.setValues(result[0]);
							if (result[0].UslugaComplexTariff_begDate || result[0].UslugaComplexTariff_endDate) {
								wnd.DateRange.setValue(result[0].UslugaComplexTariff_begDate + ' - ' + result[0].UslugaComplexTariff_endDate);
							}
							if (result[0].UslugaComplex_id && result[0].UslugaComplex_id > 0) {
								wnd.usluga_combo.setValueById(result[0].UslugaComplex_id);
							}
						}
						wnd.setDisabled(wnd.action == 'view');
						loadMask.hide();
					},
					url:'/?c=Usluga&m=loadUslugaComplexTariffLlo'
				});
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;

		this.DateRange = new Ext.form.DateRangeField({
			width: 175,
			fieldLabel: lang['period'],
			hiddenName: 'UslugaComplexTariff_DateRange',
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});

		this.usluga_combo = new sw.Promed.SwBaseRemoteCombo({
			fieldLabel: lang['naimenovanie'],
			hiddenName: 'UslugaComplex_id',
			allowBlank: false,
			anchor: '100%',
			mode: 'remote',
			minChars: 1,
			editable: true,
			codeField: 'UslugaComplex_Code',
			triggerAction: 'all',
			displayField:'UslugaComplex_Name',
			valueField: 'UslugaComplex_id',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table style="border:0;"><td style="width:50px;color:red;">{UslugaComplex_Code}</td><td nowrap>{UslugaComplex_Name}&nbsp;</td></tr></table>',
				'</div></tpl>'
			),
			initComponent: function() {
				sw.Promed.SwContragentCombo.superclass.initComponent.apply(this, arguments);
				this.store = new Ext.data.JsonStore({
					url: '/?c=Usluga&m=loadUslugaComplexCombo',
					key: 'UslugaComplex_id',
					autoLoad: false,
					fields: [
						{name: 'UslugaComplex_id', type:'int'},
						{name: 'UslugaComplex_Code', type:'int'},
						{name: 'UslugaComplex_Name', type:'string'}
					]
				});
			},
			setValueById: function(id) {
				var combo = this;
				combo.store.baseParams.UslugaComplex_id = id;
				combo.store.load({
					callback: function(){
						combo.setValue(id);
						combo.store.baseParams.UslugaComplex_id = null;
					}
				});
			}
		});
		this.usluga_combo.getStore().baseParams = {
			UslugaCategory_Code: 15 //Услуги ЛЛО
		};

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'UslugaComplexTariffLloEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 100,
				collapsible: true,
				url:'/?c=Usluga&m=saveUslugaComplexTariffLlo',
				items: [{
					xtype: 'hidden',
					name: 'UslugaComplexTariff_id'
				},
				this.usluga_combo,
				{
					xtype: 'numberfield',
					fieldLabel: lang['stavka_rub'],
					name: 'UslugaComplexTariff_Tariff',
					width: 175,
					allowBlank: false
				},
				this.DateRange
				]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swUslugaComplexTariffLloEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('UslugaComplexTariffLloEditForm').getForm();
	}	
});