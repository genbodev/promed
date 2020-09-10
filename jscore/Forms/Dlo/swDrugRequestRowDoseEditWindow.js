/**
* swDrugRequestRowDoseEditWindow - окно редактирования медикамента в спецификации заявки врача
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      08.2014
* @comment      
*/
sw.Promed.swDrugRequestRowDoseEditWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 197,
	title: lang['redaktirovanie_dozirovok'],
	layout: 'border',
	id: 'DrugRequestRowDoseEditWindow',
	modal: true,
	shim: false,
	width: 600,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var wnd = this;
		var params = new Object();
		Ext.apply(params, wnd.form.getValues());

		if (!wnd.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.base_form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		//wnd.onSave(params);
		this.form.submit({
			failure: function(result_form, action) {
				//wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				//wnd.getLoadMask().hide();
				wnd.callback();
			}
		});
		return true;		
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRequestRowDoseEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugRequestRow_id = null;
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
		if ( arguments[0].DrugRequestRow_id ) {
			this.DrugRequestRow_id = arguments[0].DrugRequestRow_id;
		}
		this.form.reset();

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (wnd.action) {
			case 'edit':
				wnd.form.setValues(arguments[0]);
				loadMask.hide();
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;	

		var form = new Ext.Panel({
			//autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			autoHeight: true,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'DrugRequestRowDoseEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 100,
				collapsible: true,
				url:'/?c=MzDrugRequest&m=saveDrugRequestRowDose',
				items: [{
					xtype: 'hidden',
					fieldLabel: lang['identifikator_stroki'],
					name: 'DrugRequestRow_id'
				}, {
					xtype: 'hidden',
					name: 'Okei_oid'
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						fieldLabel: lang['medikament'],
						name: 'Drug_Name',
						anchor: '100%',
						disabled: true
					}, {
						xtype: 'numberfield',
						fieldLabel: lang['razovaya_doza'],
						name: 'DrugRequestRow_DoseOnce',
						maxLength: 30,
						allowNegative: false,
						allowBlank: true
					}, {
						xtype: 'numberfield',
						fieldLabel: lang['sutochnaya_doza'],
						name: 'DrugRequestRow_DoseDay',
						maxLength: 30,
						allowNegative: false,
						allowBlank: true
					}, {
						xtype: 'numberfield',
						fieldLabel: lang['kursovaya_doza'],
						name: 'DrugRequestRow_DoseCource',
						maxLength: 30,
						allowNegative: false,
						allowBlank: true
					}/*, {
						xtype: 'swcommonsprcombo',
						fieldLabel: lang['ed_izm'],
						hiddenName: 'Okei_oid',
						sortField:'Okei_Code',
						comboSubject: 'Okei',
						displayedField: 'Okei_Name',
						width: 249,
						allowBlank: true
					}*/]
				}]
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
		sw.Promed.swDrugRequestRowDoseEditWindow.superclass.initComponent.apply(this, arguments);
		this.base_form = this.findById('DrugRequestRowDoseEditForm');
		this.form = this.base_form.getForm();
	}	
});