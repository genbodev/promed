/**
 * swStorageMedPersonalEditWindow - окно редактирования/добавления сотрудника склада.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			09.07.2014
 */

sw.Promed.swStorageMedPersonalEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swStorageMedPersonalEditWindow',
	objectSrc: '/jscore/Forms/Admin/swStorageMedPersonalEditWindow.js',

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
	id: 'swStorageMedPersonalEditWindow',
	width: 600,
	height: 215,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	onDoubleMedPersonal: function(combo) {
		alert('ondouble');
		var form = this.formPanel.getForm();
		var combo = form.findField('MedPersonal_id');
		var stroagemedpersonal_id = form.findField('StorageMedPersonal_id').getValue();
		var medpersonal_id;
		if(this.owner && stroagemedpersonal_id) {
			var store = this.owner.getGrid().getStore();
			var index = store.findBy(function(rec) { return rec.get('StorageMedPersonal_id') == stroagemedpersonal_id; });
			if(index >= 0) {
				medpersonal_id = store.getAt(index).get('MedPersonal_id');
			}
		}
		sw.swMsg.alert(lang['soobschenie'], lang['dannyiy_sotrudnik_uje_ukazan_na_slujbe'], function() {
			if(medpersonal_id)
				combo.setValue(medpersonal_id);
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
					win.onDoubleMedPersonal();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
				var data = {};
				data.StorageMedPersonal_id = action.result.StorageMedPersonal_id;
				if(win.owner && win.owner.id == 'StorageMedPersonalPanel')
				{
					win.callback(win.owner,action.result.StorageMedPersonal_id);
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
				'Lpu_id'
				,'MedPersonal_id'
				,'StorageMedPersonal_begDT'
				,'StorageMedPersonal_endDT'
			];

		for(var i=0;fields.length>i;i++) {
			form.findField(fields[i]).setDisabled(!is_allow);
		}

		if (is_allow)
		{
			form.findField('MedPersonal_id').focus(true, 250);
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
			id: 'StorageMedPersonalForm',
			labelAlign: 'right',
			labelWidth: 150,
			region: 'center',
			items: [{
				anchor: '100%',
				allowBlank: false,
				fieldLabel: lang['lpu'],
				hiddenName: 'Lpu_id',
				id: 'MSEW_Lpu_id',
				tabIndex: TABINDEX_MS + 16,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var form = this.formPanel.getForm();
						if ( newValue > 0 ) {
							form.findField('MedPersonal_id').clearValue();
							form.findField('MedPersonal_id').getStore().removeAll();
							form.findField('MedPersonal_id').getStore().load({params: {Lpu_id: newValue}});
						}
					}.createDelegate(this)
				},
				listWidth: 400,
				xtype: 'swlpucombo'
			}, {
				hiddenName: 'MedPersonal_id',
				allowBlank: false,
				tabIndex: TABINDEX_MS + 17,
				xtype: 'swmedpersonalallcombo',
				loadingText: lang['idet_poisk'],
				minChars: 1,
				minLength: 1,
				minLengthText: lang['pole_doljno_byit_zapolneno'],
				fieldLabel: lang['sotrudnik'],
				listeners:
				{
					select: function(combo,record,index)
					{
						var form = this.formPanel.getForm();
						var storagemedpersonal_id = form.findField('StorageMedPersonal_id').getValue() || 0;
						if(this.owner) {
							var index = this.owner.getGrid().getStore().findBy( function(r,id){
								if(record.get('MedPersonal_id') == r.get('MedPersonal_id') && storagemedpersonal_id != r.get('StorageMedPersonal_id'))
									return true;
							});
							if(index >= 0) {
								this.onDoubleMedPersonal();
								return false;
							}
						}
						form.findField('StorageMedPersonal_begDT').setValue(record.get('WorkData_begDate'));
						form.findField('StorageMedPersonal_endDT').setValue(record.get('WorkData_endDate'));
					}.createDelegate(this)
				}
			}, {
				fieldLabel: lang['data_nachala'],
				name: 'StorageMedPersonal_begDT',
				allowBlank: false,
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_MS + 22,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['data_okonchaniya'],
				name: 'StorageMedPersonal_endDT',
				allowBlank: true,
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_MS + 23,
				xtype: 'swdatefield'
			}, {
				name: 'StorageMedPersonal_id',
				xtype: 'hidden'
			}, {
				name: 'Storage_id',
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
					{ name: 'StorageMedPersonal_id' },
					{ name: 'Lpu_id' },
					{ name: 'Storage_id' },
					{ name: 'MedPersonal_id' },
					{ name: 'StorageMedPersonal_begDT' },
					{ name: 'StorageMedPersonal_endDT' }
				]),
			timeout: 600,
			url: '/?c=Storage&m=saveStorageMedPersonal'
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
					onTabElement: 'MSEW_Lpu_id',
					tabIndex: TABINDEX_MS + 30,
					text: BTN_FRMCANCEL
				}],
			items: [
				this.formPanel
			]
		});
		sw.Promed.swStorageMedPersonalEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swStorageMedPersonalEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
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
			lpu_combo = form.findField('Lpu_id'),
			mp_combo = form.findField('MedPersonal_id');

		form.setValues(arguments[0]);
		switch (this.action) {
			case 'view':
				this.setTitle(lang['sotrudnik_prosmotr']);
				break;

			case 'edit':
				this.setTitle(lang['sotrudnik_redaktirovanie']);
				break;

			case 'add':
				this.setTitle(lang['sotrudnik_dobavlenie']);
				break;

			default:
				return false;
				break;
		}

		var loadCombo = function(){
			if ( lpu_combo.getStore().getCount() == 0 ) {
				lpu_combo.getStore().load({
					callback: function(records, options, success) {
						if ( !success ) {
							lpu_combo.getStore().removeAll();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_lpu']);
							return false;
						}
						lpu_combo.setValue(lpu_combo.getValue());
					}
				});
			}
			mp_combo.getStore().removeAll();
			mp_combo.getStore().load(
				{
					params: {Lpu_id: lpu_combo.getValue()},
					callback: function(r,o,s)
					{
						mp_combo.setValue(mp_combo.getValue());
					}
				});
		};
		if(this.action == 'add')
		{
			win.allowEdit(true);
			loadCombo();
			this.syncSize();
			this.doLayout();
		}
		else
		{
			win.allowEdit(false);
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
				},
				params: {
					StorageMedPersonal_id: form.findField('StorageMedPersonal_id').getValue()
				},
				success: function() {
					loadCombo();
					win.getLoadMask().hide();
					if(win.action == 'edit')
					{
						win.allowEdit(true);
					}
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=Storage&m=loadStorageMedPersonalForm'
			});
		}
	}
});