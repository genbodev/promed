/**
* swMorbusOnkoSpecTreatWindow - окно редактирования "Специальное лечение"
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

sw.Promed.swMorbusOnkoSpecTreatWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	winTitle: lang['spetsialnoe_lechenie'],
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 930,
	height: 300,
	maximizable: true,
	autoScroll: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	doSave:  function() {
		var that = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					that.findById('MorbusOnkoSpecTreatEditForm').getFirstInvalidEl().focus(true);
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
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var formParams = this.form.getValues();
		if (this.EvnPL_id)
			formParams.EvnPL_id = this.EvnPL_id;
		var lateCompls = this.OnkoLateComplTreatTypePanel.getValues();
		formParams.lateCompls = (lateCompls.length > 1 ? lateCompls.join(',') : lateCompls);
		Ext.Ajax.request({
			params: formParams,
			method: 'POST',
			success: function (result) {
				loadMask.hide();
				if (result.responseText) {
					var response = Ext.util.JSON.decode(result.responseText);
					formParams.MorbusOnkoSpecTreat_id = response.MorbusOnkoSpecTreat_id;
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
			url:'/?c=MorbusOnkoSpecifics&m=saveMorbusOnkoSpecTreatEditForm'
		});
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	onLoadForm: function(formParams) {
		/*var accessType = formParams.accessType || 'edit';
		this.setFieldsDisabled(this.action == 'view' || accessType == 'view');
		if (Ext.isArray(formParams.disabledDates) && formParams.disabledDates.length > 0) {
			this.form.findField('MorbusOnkoSpecTreat_specSetDT').setDisabledDates(formParams.disabledDates);
			this.form.findField('MorbusOnkoSpecTreat_specDisDT').setDisabledDates(formParams.disabledDates);
		} else {
			this.form.findField('MorbusOnkoSpecTreat_specSetDT').setDisabledDates(null);
			this.form.findField('MorbusOnkoSpecTreat_specDisDT').setDisabledDates(null);
		}
		var max_date = formParams.maxDate ? Date.parseDate(formParams.maxDate, 'd.m.Y') : null;
		var min_date = formParams.minDate ? Date.parseDate(formParams.minDate, 'd.m.Y') : null;
		//log(formParams,min_date, max_date);
		this.form.findField('MorbusOnkoSpecTreat_specSetDT').setMinValue(min_date);
		this.form.findField('MorbusOnkoSpecTreat_specSetDT').setMaxValue(max_date);
		this.form.findField('MorbusOnkoSpecTreat_specDisDT').setMinValue(min_date);
		this.form.findField('MorbusOnkoSpecTreat_specDisDT').setMaxValue(max_date);*/
		this.form.findField('MorbusOnkoSpecTreat_specSetDT').setDisabledDates(null);
		this.form.findField('MorbusOnkoSpecTreat_specDisDT').setDisabledDates(null);
		this.form.findField('MorbusOnkoSpecTreat_specSetDT').setMinValue(null);
		this.form.findField('MorbusOnkoSpecTreat_specSetDT').setMaxValue(null);
		this.form.findField('MorbusOnkoSpecTreat_specDisDT').setMinValue(null);
		this.form.findField('MorbusOnkoSpecTreat_specDisDT').setMaxValue(null);
		if(getRegionNick() == 'perm'){
			var combo_tt = this.form.findField('TumorPrimaryTreatType_id');
			combo_tt.fireEvent('change',combo_tt,combo_tt.getValue());
		}
	},
	show: function() {
		var that = this;
		sw.Promed.swMorbusOnkoSpecTreatWindow.superclass.show.apply(this, arguments);
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		this.action = arguments[0].action || 'add';
		this.callback = Ext.emptyFn;
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		this.onHide = Ext.emptyFn;
		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		this.form.reset();
		if ( 'add' != this.action && !arguments[0].formParams.MorbusOnkoSpecTreat_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_verno_ukazanyi_vhodnyie_dannyie_1'], function() { that.hide(); });
			return false;
		}
		if ( 'add' == this.action && !arguments[0].formParams.MorbusOnko_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_verno_ukazanyi_vhodnyie_dannyie_2'], function() { that.hide(); });
			return false;
		}
		if (!Ext.isEmpty(arguments[0].formParams.EvnPL_id))
			this.EvnPL_id = arguments[0].formParams.EvnPL_id;

		switch (this.action) {
			case 'add':
				this.setTitle(this.winTitle +lang['_dobavlenie']);
				break;
			case 'edit':
				this.setTitle(this.winTitle +lang['_redaktirovanie']);
				break;
			case 'view':
				this.setTitle(this.winTitle +lang['_prosmotr']);
				break;
		}
		
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		if ('add' == this.action) {
			that.form.setValues(arguments[0].formParams);
            loadMask.hide();
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					loadMask.hide();
					that.hide();
				},
				params:{
					MorbusOnkoVizitPLDop_id: arguments[0].formParams.MorbusOnkoVizitPLDop_id,
					MorbusOnkoLeave_id: arguments[0].formParams.MorbusOnkoLeave_id,
					MorbusOnkoDiagPLStom_id: arguments[0].formParams.MorbusOnkoDiagPLStom_id,
					MorbusOnko_id: arguments[0].formParams.MorbusOnko_id
				},
				method: 'POST',
				success: function (response) {
					loadMask.hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0]) {
						if(result[0].unclosedTreat === true){
							sw.swMsg.show(
							{
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								msg: 'Существует незакрытое специальное лечение - добавление нового невозможно',
								title: lang['oshibka']
							});
							that.hide();
						}
						that.onLoadForm(result[0]);
					}
				},
				url:'/?c=MorbusOnkoSpecifics&m=getMorbusOnkoSpecTreatDisabledDates'
			});
			this.OnkoLateComplTreatTypePanel.setValues([null]);
		} else {
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					loadMask.hide();
					that.hide();
				},
				params:{
					MorbusOnkoSpecTreat_id: arguments[0].formParams.MorbusOnkoSpecTreat_id
				},
				method: 'POST',
				success: function (response) {
					loadMask.hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0]) {
						that.form.setValues(result[0]);
						if(result[0].lateCompls){
							that.OnkoLateComplTreatTypePanel.setValues(result[0].lateCompls);
						} else {
							that.OnkoLateComplTreatTypePanel.setValues([null]);
						}
						that.onLoadForm(result[0]);
					}
				},
				url:'/?c=MorbusOnkoSpecifics&m=loadMorbusOnkoSpecTreatEditForm'
			});
		}
		return true;
	},
	initComponent: function() {
		var that = this;
		this.OnkoLateComplTreatTypePanel = new sw.Promed.AddOnkoComplPanel({
			objectName: 'OnkoLateComplTreatType',
			fieldLabelTitle: lang['pozdnee_oslojnenie_lecheniya'],
			win: this,
			width: 880,
			buttonAlign: 'left',
			buttonLeftMargin: 150,
			labelWidth: 300,
			style: 'background: #DFE8F6',
			onChange: function() {
				// отфильтровать ресурсы по услуге
				//form.filterResource();
			}
		});
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: false,
			id: 'MorbusOnkoSpecTreatEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			labelWidth: 300,
			labelAlign: 'right',
			region: 'center',
			items: [{
				name: 'MorbusOnkoSpecTreat_id',
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
				fieldLabel: langs('Дата начала'),
				name: 'MorbusOnkoSpecTreat_specSetDT',
                endDateField: 'MorbusOnkoSpecTreat_specDisDT',
				xtype: 'swdatefield',
				allowBlank: false,
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				fieldLabel: lang['data_okonchaniya'],
				begDateField: 'MorbusOnkoSpecTreat_specSetDT',
				name: 'MorbusOnkoSpecTreat_specDisDT',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				fieldLabel: lang['provedennoe_lechenie_pervichnoy_opuholi'],
				hiddenName: 'TumorPrimaryTreatType_id',
				xtype: 'swcommonsprlikecombo',
				sortField:'TumorPrimaryTreatType_Code',
				comboSubject: 'TumorPrimaryTreatType',
				width: 400,
				listeners: {
					'change': function(c,n){
						if(getRegionNick() == 'perm'){
							if(n == 2){
								that.form.findField('TumorRadicalTreatIncomplType_id').enable();
							} else {
								that.form.findField('TumorRadicalTreatIncomplType_id').disable();
							}
						}
					}
				}
			}, {
				fieldLabel: lang['prichinyi_nezavershennosti_radikalnogo_lecheniya'],
				hiddenName: 'TumorRadicalTreatIncomplType_id',
				xtype: 'swcommonsprlikecombo',
				sortField:'TumorRadicalTreatIncomplType_Code',
				comboSubject: 'TumorRadicalTreatIncomplType',
				width: 400
			}, {
				fieldLabel: lang['sochetanie_vidov_lecheniya'],
				hiddenName: 'OnkoCombiTreatType_id',
				xtype: 'swcommonsprlikecombo',
				sortField:'OnkoCombiTreatType_Code',
				comboSubject: 'OnkoCombiTreatType',
				width: 400
			}, 
			this.OnkoLateComplTreatTypePanel
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusOnkoSpecTreat_id'}, 
				{name: 'MorbusOnko_id'}, 
				{name: 'MorbusOnkoLeave_id'}, 
				{name: 'MorbusOnkoVizitPLDop_id'}, 
				{name: 'MorbusOnkoDiagPLStom_id'}, 
				{name: 'MorbusOnkoSpecTreat_specSetDT'}, 
				{name: 'MorbusOnkoSpecTreat_specDisDT'}, 
				{name: 'TumorPrimaryTreatType_id'},
				{name: 'TumorRadicalTreatIncomplType_id'},
				{name: 'OnkoCombiTreatType_id'},
				{name: 'OnkoLateComplTreatType_id'}
			])
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					that.doSave();
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
					that.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[that.formPanel]
		});
		sw.Promed.swMorbusOnkoSpecTreatWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.formPanel.getForm();
	}
});