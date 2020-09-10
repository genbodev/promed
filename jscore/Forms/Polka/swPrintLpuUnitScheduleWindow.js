/**
* swPrintLpuUnitScheduleWindow - Список записанных по всем врачам
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2011-2012 Swan Ltd.
* @author       Storozhev Dmitry
* @version      09.10.2012
*/

sw.Promed.swPrintLpuUnitScheduleWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 640,
	autoHeight: true,
	
	modal: true,
	resizable: false,
	plain: true,
	title: lang['spisok_zapisannyih_po_vsem_vracham'],

	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},
	
	show: function() {
		sw.Promed.swPrintLpuUnitScheduleWindow.superclass.show.apply(this, arguments);
		
		var frm = this.Form.getForm();
		frm.findField('Date').setValue((new Date()).format('d.m.Y'));
		
		var current_ms = sw.Promed.MedStaffFactByUser.current;
		if( !current_ms ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_opredelit_tekuschee_rab_mesto']);
			return false;
		}
		
		frm.findField('LpuUnit_id').setDisabled(current_ms.LpuUnit_id > 0);
		frm.findField('LpuUnit_id').getStore().load({
			params: {
				lpu_id: getGlobalOptions().lpu_id
			},
			scope: frm.findField('LpuUnit_id'),
			callback: function() {
				if ( current_ms.LpuUnit_id > 0 ) {
					this.setValue(current_ms.LpuUnit_id);
				} else if ( current_ms.Lpu_id > 0 ) {
					this.getStore().filterBy(function(r) {
						return r.get('LpuUnitType_id') == 2;
					});
					this.getStore().loadData(getStoreRecords(this.getStore()));
				}
			}
		});
	},
	
	printLpuUnitSchedule: function() {
		var frm = this.Form.getForm();
		if( !frm.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['zapolnite_obyazatelnyie_polya']);
			return;
		}
		window.open('/?c=Reg&m=printLpuUnitSchedule&Date='+frm.findField('Date').getValue().format('d.m.Y')+'&LpuUnit_id='+frm.findField('LpuUnit_id').getValue(),  '_blank');
	},

	initComponent: function() {
		this.Form = new Ext.FormPanel({
			frame: true,
			labelAlign: 'right',
			items: [{
				layout: 'form',
				items: [{
					xtype: 'swdatefield',
					name: 'Date',
					allowBlank: false,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					fieldLabel: lang['data']
				}, {
					xtype: 'swlpuunitglobalcombo',
					allowBlank: false,
					anchor: '100%',
					fieldLabel: lang['podrazdelenie']
				}]
			}]
		});
		
    	Ext.apply(this, {
			items: [this.Form],
			buttons: [{
				text: lang['pechat'],
				tooltip: lang['pechat'],
				iconCls: 'print16',
				scope: this,
				handler: this.printLpuUnitSchedule
			},
			'-',
			{
				text: lang['zakryit'],
				tooltip: lang['zakryit'],
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}],
			buttonAlign: 'right'
		});
		sw.Promed.swPrintLpuUnitScheduleWindow.superclass.initComponent.apply(this, arguments);
	}
});