/**
* swNormCostItemEditWindow - окно просмотра, добавления и редактирования норматива
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @author       Dmitry Vlasenko
*/

/*NO PARSE JSON*/
sw.Promed.swNormCostItemEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swNormCostItemEditWindow',
	objectSrc: '/jscore/Forms/Common/swNormCostItemEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'form',
	title: lang['normativ_rashoda'],
	draggable: true,
	id: 'swNormCostItemEditWindow',
	width: 500,
	autoHeight: true,
	modal: true,
	plain: true,
	resizable: false,
	doSave: function() {
		var win = this,
			form = this.formPanel.getForm(),
			params = {};

		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		win.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();				
				win.action = 'edit';
				var data = {};
				var id = action.result.NormCostItem_id;
				form.findField('NormCostItem_id').setValue(id);
				data.NormCostItem_id = id;
				win.owner.refreshRecords(win.owner, id);
				win.hide();
			}
		});
	},
	initComponent: function() {
		var win = this;
		
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 120,
			region: 'north',
			items: [{
				xtype: 'swdrugnomensimplecombo',
				fieldLabel : lang['naimenovanie'],
				width: 300,
				hiddenName: 'DrugNomen_id',
				value: '',
				allowBlank: false
			}, {
				fieldLabel : lang['kolichestvo'],
				name: 'NormCostItem_Kolvo',
				xtype: 'numberfield',
				allowBlank: false,
				minValue: 0.1
			}, {
				fieldLabel: lang['ed_izmereniya'],
				hiddenName: 'Unit_id',
				xtype: 'swcommonsprcombo',
				editable: true,
				prefix:'lis_',
				allowBlank: false,
				sortField:'Unit_Name',
				comboSubject: 'unit',
				width: 200
			}, {
				fieldLabel: lang['analizator'],
				width: 200,
				anchor: '',
				hiddenName: 'Analyzer_id',
				allowBlank: true,
				xtype: 'swanalyzercombo'
			}, {
				name: 'AnalyzerTest_id',
				xtype: 'hidden'
			}, {
				name: 'UslugaComplex_id',
				xtype: 'hidden'
			}, {
				name: 'NormCostItem_id',
				xtype: 'hidden'
			}, {
				name: 'MedService_id',
				xtype: 'hidden'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey())  {
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
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: function() { 
					//
				}
			}, 
			[
				{ name: 'NormCostItem_id' },
				{ name: 'AnalyzerTest_id' },
				{ name: 'UslugaComplex_id' },
				{ name: 'DrugNomen_id' },
				{ name: 'Analyzer_id' },
				{ name: 'NormCostItem_Kolvo' },
				{ name: 'Unit_id' }
			]),
			timeout: 600,
			url: '/?c=NormCostItem&m=saveNormCostItem'
		});		

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_GL + 29,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					win.hide();
				},
				onTabElement: 'GREW_Marker_Word',
				tabIndex: TABINDEX_GL + 31,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swNormCostItemEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swNormCostItemEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0]) {
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.owner = arguments[0].owner || null;		

		this.center();

		var win = this,
			base_form = this.formPanel.getForm(); 

		base_form.reset();
		base_form.setValues(arguments[0]);
		
		base_form.findField('Analyzer_id').getStore().load({
			params: {
				MedService_id: base_form.findField('MedService_id').getValue()
			}
		});
		
		switch (this.action) {
			case 'view':
				this.setTitle(lang['normativ_rashoda_prosmotr']);
				break;
			case 'edit':
				this.setTitle(lang['normativ_rashoda_redaktirovanie']);
				break;
			case 'add':
				this.setTitle(lang['normativ_rashoda_dobavlenie']);
				break;
			break;
		}

		if (this.action == 'add') {
			win.enableEdit(true);
			this.syncSize();
			this.doLayout();
			base_form.findField('DrugNomen_id').getStore().load();
		} else {
			win.enableEdit(false);
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
				},
				params: {
					NormCostItem_id: base_form.findField('NormCostItem_id').getValue()
				},
				success: function(form, action) {					
					win.getLoadMask().hide();
					if(win.action == 'edit') {
						win.enableEdit(true);
					}
					
					if (!Ext.isEmpty(base_form.findField('DrugNomen_id').getValue())) {
						base_form.findField('DrugNomen_id').getStore().load({
							params: {
								'DrugNomen_id': base_form.findField('DrugNomen_id').getValue()
							},
							callback: function() {
								base_form.findField('DrugNomen_id').setValue(base_form.findField('DrugNomen_id').getValue());
							}
						});
					}
				},
				url: '/?c=NormCostItem&m=loadNormCostItemEditForm'
			});
		}
	}
});
