/**
* Службы: Форма выбора службы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      12.01.2012
*/

sw.Promed.swSelectMedServiceWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['vyibor_slujbyi'],
	modal: true,
	height: 150,
	width: 500,
	shim: false,
	plain: true,
	ARMType: null,
	onSelect: Ext.emptyFn,
	layout: 'fit',
	objectName: 'swSelectMedServiceWindow',
	closeAction: 'hide',
	id: 'swSelectMedServiceWindow',
	objectSrc: '/jscore/Forms/Mse/swSelectMedServiceWindow.js',
	filterMedServiceList: function() {
		var
			base_form = this.Form.getForm(),
			index,
			MedService_id = base_form.findField('MedService_id').getValue(),
			onlyMyLpu = base_form.findField('onlyMyLpu').getValue(),
			win = this;

		base_form.findField('MedService_id').lastQuery = '';
		base_form.findField('MedService_id').getStore().clearFilter();
		base_form.findField('MedService_id').getStore().filterBy(function(rec) {
			return (
				rec.get('MedServiceType_id') == win.MedServiceType_id
				&& (!onlyMyLpu || rec.get('Lpu_id') == getGlobalOptions().lpu_id)
			)
		});

		if ( !Ext.isEmpty(MedService_id) ) {
			index = base_form.findField('MedService_id').getStore().findBy(function(rec) {
				return rec.get('MedService_id') == MedService_id;
			});

			if ( index == -1 ) {
				base_form.findField('MedService_id').clearValue();
			}
		}
	},
	show: function() {
		sw.Promed.swSelectMedServiceWindow.superclass.show.apply(this, arguments);

		if ( arguments[0].onSelect && arguments[0].ARMType ) {
			this.onSelect = arguments[0].onSelect;
			this.ARMType = arguments[0].ARMType;
		}
		else {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}

		this.isRecord = (arguments[0].isRecord && arguments[0].isRecord === true);
		this.action = arguments[0].action || 'edit';
		this.MedService_id = arguments[0].MedService_id || null;
		this.MedServiceType_id = 0;

		var
			base_form = this.Form.getForm(),
			combo = base_form.findField('MedService_id'),
			lm = this.getLoadMask(lang['zagruzka']),
			win = this;

		base_form.reset();

		switch ( this.ARMType ) {
			case 'vk':
				this.MedServiceType_id = 1;
			break;

			case 'mse':
				this.MedServiceType_id = 2;
			break;

			case 'htm':
				this.MedServiceType_id = 39;
			break;
		}

		if ( !this.isRecord || this.ARMType.inlist([ 'htm' ]) ) {
			base_form.findField('onlyMyLpu').setValue(true);
		}

		if ( !this.isRecord ) {
			base_form.findField('onlyMyLpu').disable();
		}
		else {
			base_form.findField('onlyMyLpu').enable();
		}
		
		combo.setDisabled(this.action == 'view');

		lm.show();
		combo.getStore().load({
			callback: function(){
				lm.hide();
				win.filterMedServiceList();
				if (win.MedService_id) {
					combo.setValue(win.MedService_id);
				}
				combo.focus(true, 250);
			}.createDelegate(this),
			params: {
				Lpu_isAll: win.MedServiceType_id == 39 ? 1 : 0,
				Lpu_id: getGlobalOptions().lpu_id,
				isMse: win.MedServiceType_id == 2 ? 1 : 0,
				isHtm: win.MedServiceType_id == 39 ? 1 : 0
			}
		});
	},
	
	doSelect: function()
	{
		var form = this.Form.getForm(),
			combo = form.findField('MedService_id'),
			idx = combo.getStore().find(combo.valueField, new RegExp('^' + combo.getValue() + '$')),
			record = combo.getStore().getAt(idx);
		if(!record || !form.isValid())
			return false;
		getGlobalOptions().CurMedService_id = record.get('MedService_id');
		getGlobalOptions().CurLpuSection_id = record.get('LpuSection_id');
		getGlobalOptions().CurLpuUnitType_id = record.get('LpuUnitType_id');
		getGlobalOptions().CurMedService_Name = record.get('MedService_Name');
		this.onSelect(record.data);
		this.hide();
	},
	doCancel: function(){
		var form = this.Form.getForm();
		this.onSelect(false);
		this.hide();
	},	
	initComponent: function()
	{
		var cur_win = this;
		
		this.Form = new Ext.form.FormPanel({
			layout: 'form',
			frame: true,
			bodyStyle: 'padding: 5px;',
			labelAlign: 'right',
			labelWidth: 100,
			items: [
				{
					xtype: 'swcheckbox',
					name: 'onlyMyLpu',
					// hidden: getRegionNick() == 'perm',
					// hideLabel: getRegionNick() == 'perm',
					fieldLabel: 'Только своя МО',
					listeners: {
						'check': function (comp,newvalue) {
							this.filterMedServiceList();
						}.createDelegate(this)
					}
				},
				{
					xtype: 'swmedserviceglobalcombo',
					allowBlank: false,
					editable: false,
					anchor: '100%'
				}
			]
		});
		
		Ext.apply(this,	{
			items: [ this.Form ],
			buttonAlign: "right",
			buttons: [
				{
					handler: this.doSelect.createDelegate(this),
					iconCls: 'ok16',
					text: lang['vyibrat']
				}, {
					handler: this.doCancel.createDelegate(this),
					iconCls: 'diag-hist16',
					text: 'Пропустить',
					hidden: true
				}, {
					text: '-'
				},
				HelpButton(this),
				{
					handler: this.hide.createDelegate(this, []),
					text: lang['zakryit'],
					tabIndex: -1,
					tooltip: lang['zakryit'],
					iconCls: 'cancel16'
				}
			]
		});
		sw.Promed.swSelectMedServiceWindow.superclass.initComponent.apply(this, arguments);
	}
});