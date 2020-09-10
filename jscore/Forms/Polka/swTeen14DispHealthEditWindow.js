/**
* swTeen14DispHealthEditWindow - форма редактирования роста человека
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Person
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author       Ivan Pshenitcyn aka IVP (ipshon@gmail.com)
* @version      07.08.2011
* @comment      Префикс для id компонентов T14DHEW (Teen14DispHealthEditWindow)
*/

sw.Promed.swTeen14DispHealthEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var data = base_form.getValues();
		
		data['DeseaseFuncType_Name'] = base_form.findField('DeseaseFuncType_id').getRawValue();
		data['DiagType_Name'] = base_form.findField('DiagType_id').getRawValue();
		data['DispRegistrationType_Name'] = base_form.findField('DispRegistrationType_id').getRawValue();
		data['EvnVizitDispTeen14_isFirstDetected_Name'] = base_form.findField('EvnVizitDispTeen14_isFirstDetected').getRawValue();

		this.callback(data);
		this.hide();

		return true;
	},
	draggable: true,
	id: 'Teen14DispHealthEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'Teen14DispHealthEditForm',
			labelAlign: 'right',
			labelWidth: 180,
			url: '',
			items: [{
				name: 'Teen14DispSpecType_id',
				value: 0,
				xtype: 'hidden'
			}, {
				autoHeight: true,
				xtype: 'fieldset',
				labelWidth: 168,
				title: lang['diagnoz'],
				items: [{
					allowBlank: true,
					disabled: true,
					hiddenName: 'Diag_id',
					id: 'T14DHEW_DiagCombo',
					listWidth: 350,
					width: 350,
					xtype: 'swdiagcombo'
				}, {					
					allowBlank: false,
					comboSubject: 'DeseaseFuncType',
					disabled: false,
					fieldLabel: lang['zabolevanie'],
					width: 350,
					tabIndex: 270,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					comboSubject: 'DiagType',
					disabled: false,
					fieldLabel: lang['tip_diagnoza'],
					width: 350,
					tabIndex: 271,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					comboSubject: 'DispRegistrationType',
					disabled: false,
					fieldLabel: lang['dispansernyiy_uchet'],
					width: 350,
					tabIndex: 272,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					comboSubject: 'YesNo',
					hiddenName: 'EvnVizitDispTeen14_isFirstDetected',
					disabled: false,
					fieldLabel: lang['vyiyavlen_vpervyie'],
					width: 350,
					tabIndex: 273,
					xtype: 'swcommonsprcombo'
				}]
			}, {
				allowBlank: false,
				comboSubject: 'RecommendationsTreatmentType',
				disabled: false,
				fieldLabel: lang['rekom-ii_po_daln_lech'],
				width: 350,
				tabIndex: 274,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				comboSubject: 'YesNo',
				hiddenName: 'EvnVizitDispTeen14_isVMPRecommented',
				disabled: false,
				fieldLabel: lang['vmp_rekomendovana'],
				width: 350,
				tabIndex: 275,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				comboSubject: 'RecommendationsTreatmentDopType',
				hiddenName: 'RecommendationsTreatmentDopType_id',
				disabled: false,
				fieldLabel: lang['rekom-ii_po_dop_obsled'],
				width: 350,
				tabIndex: 276,
				xtype: 'swcommonsprcombo'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: 277,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( !this.buttons[0].hidden ) {
						this.buttons[0].focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {					
						this.FormPanel.getForm().findField('DeseaseFuncType_id').focus(true);					
				}.createDelegate(this),
				tabIndex: 278,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swTeen14DispHealthEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('Teen14DispHealthEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: true
	}],
	listeners: {
		'beforehide': function(win) {
			// 
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swTeen14DispHealthEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = 'edit';
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
	
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);
		
		if ( !(arguments[0].formParams['RecommendationsTreatmentType_id'] > 0) )
			base_form.findField('RecommendationsTreatmentType_id').setValue(1);
		if ( !(arguments[0].formParams['RecommendationsTreatmentDopType_id'] > 0) )
			base_form.findField('RecommendationsTreatmentDopType_id').setValue(1);
		if ( !(arguments[0].formParams['EvnVizitDispTeen14_isFirstDetected'] > 0) )
		{
			if ( arguments[0].formParams['DopDispDiagType_id'] == 1 )
				base_form.findField('EvnVizitDispTeen14_isFirstDetected').setValue(1);
			else
				base_form.findField('EvnVizitDispTeen14_isFirstDetected').setValue(2);
		}
			
		
		base_form.findField('Diag_id').getStore().load({
			callback: function() {
				base_form.findField('Diag_id').setValue(base_form.findField('Diag_id').getValue());
				base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
			},
			params: {
				where: "where DiagLevel_id = 4 and Diag_id = " + arguments[0].formParams['Diag_id']
			}
		});
		
		base_form.clearInvalid();
		
		base_form.findField('DeseaseFuncType_id').focus(true, 300);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		
		this.setTitle(lang['sostoyanie_zdorovya_redaktirovanie']);
	},
	width: 600
});