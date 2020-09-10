/**
* swLpuSectionProfileMedServiceEditWindow - окно просмотра, добавления и редактирования профилей консультирования
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
sw.Promed.swLpuSectionProfileMedServiceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swLpuSectionProfileMedServiceEditWindow',
	objectSrc: '/jscore/Forms/Admin/swLpuSectionProfileMedServiceEditWindow.js',

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
	id: 'swLpuSectionProfileMedServiceEditWindow',
	width: 500,
	height: 115,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	onDoubleLpuSectionProfile: function() {
		var form = this.formPanel.getForm();
		var combo = form.findField('LpuSectionProfile_id');
		var LpuSectionProfileMedService_id = form.findField('LpuSectionProfileMedService_id').getValue();
		var LpuSectionProfile_id;
		if (this.owner && LpuSectionProfileMedService_id) {
			var store = this.owner.getGrid().getStore();
			var index = store.findBy(function(rec) { return rec.get('LpuSectionProfileMedService_id') == LpuSectionProfileMedService_id; });
			if(index >= 0) {
				LpuSectionProfile_id = store.getAt(index).get('LpuSectionProfile_id');
			}
		}
		sw.swMsg.alert(lang['soobschenie'], lang['dannyiy_profil_uje_ukazan_na_slujbe'], function() {
			if(LpuSectionProfile_id)
				combo.setValue(LpuSectionProfile_id);
			else
				combo.clearValue();
			combo.focus(true, 250);
		});
	},
	submit: function() {
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
				if(action.result.Error_Code && action.result.Error_Code == 7)
					win.onDoubleLpuSectionProfile();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
				var data = {};
				data.LpuSectionProfileMedService_id = action.result.LpuSectionProfileMedService_id;
				if (win.owner && win.owner.id == 'LpuSectionProfileMedServicePanel') {
					win.callback(win.owner,action.result.LpuSectionProfileMedService_id);
				} else {
					win.callback(data);
				}
			}
		});
	},
	allowEdit: function(is_allow) {
		var win = this,
			form = win.formPanel.getForm(),
			save_btn = win.buttons[0],
			fields = ['LpuSectionProfile_id'
				//,'LpuSectionProfileMedService_begDT'
				//,'LpuSectionProfileMedService_endDT'
			];

		for(var i=0;fields.length>i;i++) {
			form.findField(fields[i]).setDisabled(!is_allow);
		}

		if (is_allow)
		{
			form.findField('LpuSectionProfile_id').focus(true, 250);
			save_btn.show();
		}
		else
		{
			save_btn.hide();
		}
	},

	initComponent: function() {
		var win = this;
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuSectionProfileMedServiceRecordEditForm',
			labelAlign: 'right',
			labelWidth: 100,
			region: 'center',
			items: [{
                id: 'LSPMSEW_LpuSectionProfile_id',
				hiddenName: 'LpuSectionProfile_id',
				allowBlank: false,
				tabIndex: TABINDEX_MS + 17,
				xtype: 'swlpusectionprofilecombo',
                enableKeyEvents: true,
                width: 300,
				fieldLabel: lang['profil'],
				listeners:
				{
					select: function(combo,record,index)
					{
						var form = win.formPanel.getForm();
						var LpuSectionProfileMedService_id = form.findField('LpuSectionProfileMedService_id').getValue() || 0;
						if(win.owner) {
							index = win.owner.getGrid().getStore().findBy( function(r){
								return (record.get('LpuSectionProfile_id') == r.get('LpuSectionProfile_id')
                                    && LpuSectionProfileMedService_id != r.get('LpuSectionProfileMedService_id')
                                    );
							});
							if (index >= 0) {
                                win.onDoubleLpuSectionProfile();
								return false;
							}
						}
						//form.findField('LpuSectionProfileMedService_begDT').setValue(record.get('LpuSectionProfile_begDate'));
						//form.findField('LpuSectionProfileMedService_endDT').setValue(record.get('LpuSectionProfile_endDate'));
                        return true;
					}
				}
			}, {/*
				fieldLabel: lang['data_nachala'],
				name: 'LpuSectionProfileMedService_begDT',
				allowBlank: false,
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_MS + 22,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['data_okonchaniya'],
				name: 'LpuSectionProfileMedService_endDT',
				allowBlank: true,
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_MS + 23,
				xtype: 'swdatefield'
			}, {*/
				name: 'LpuSectionProfileMedService_id',
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
				{ name: 'LpuSectionProfileMedService_id' },
				{ name: 'MedService_id' },
				{ name: 'LpuSectionProfile_id' }/*,
				{ name: 'LpuSectionProfileMedService_begDT' },
				{ name: 'LpuSectionProfileMedService_endDT' }*/
			]),
			timeout: 600,
			url: '/?c=MedService&m=saveLpuSectionProfileMedService'
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
				onTabElement: 'LSPMSEW_LpuSectionProfile_id',
				tabIndex: TABINDEX_MS + 30,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swLpuSectionProfileMedServiceEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swLpuSectionProfileMedServiceEditWindow.superclass.show.apply(this, arguments);
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		// вьюфрейм с врачами служб
		this.owner = arguments[0].owner || null;
		if(!arguments[0].Lpu_id)
			arguments[0].Lpu_id = getGlobalOptions().lpu_id;

		this.doReset();
		this.center();

		var win = this,
			form = this.formPanel.getForm(),
			lsp_combo = form.findField('LpuSectionProfile_id'),
			index;

		form.setValues(arguments[0]);
		lsp_combo.getStore().clearFilter();
		lsp_combo.lastQuery = '';
		switch (this.action) {
			case 'view':
				this.setTitle(lang['profil_konsultirovaniya_prosmotr']);
			break;

			case 'edit':
				this.setTitle(lang['profil_konsultirovaniya_redaktirovanie']);
			break;

			case 'add':
				this.setTitle(lang['profil_konsultirovaniya_dobavlenie']);
			break;

			default:
				log('swLpuSectionProfileMedServiceEditWindow - action invalid');
				return false;
			break;
		}

		if (this.action == 'add') {
			win.allowEdit(true);
			lsp_combo.setBaseFilter(function (rec) {
				var setDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
				return (Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= setDate)
				&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= setDate);
			});
			this.syncSize();
			this.doLayout();
		} else {
			win.allowEdit(false);
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
				},
				params: {
					LpuSectionProfileMedService_id: form.findField('LpuSectionProfileMedService_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
					win.allowEdit(win.action == 'edit');
					if (win.action == 'edit') {
						lsp_combo.setBaseFilter(function (rec) {
							var setDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
							return (Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= setDate)
							&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= setDate);
						});
						index = lsp_combo.getStore().findBy(function(rec) {
							return (rec.get('LpuSectionProfile_id') == lsp_combo.getValue());
						});
						if ( index >= 0 ) {
							lsp_combo.setValue(lsp_combo.getValue());
							lsp_combo.fireEvent('select', lsp_combo, lsp_combo.getStore().getAt(index));
						} else {
							lsp_combo.setValue(null);
						}
					}
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=MedService&m=loadLpuSectionProfileMedServiceEditForm'
			});
		}
        return true;
	}
});
