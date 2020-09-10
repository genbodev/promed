/**
 * swLpuBuildingOfficePrintWindow - параметры печати
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 * @author      Bykov Stanislav
 * @version     12.2017
 */
sw.Promed.swLpuBuildingOfficePrintWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	id: 'swLpuBuildingOfficePrintWindow',
	layout: 'form',
	maximizable: false,
	modal: true,
	resizable: false,
	title: 'Параметры печати',
	width: 500,

	/* методы */
	doPrint: function() {
		var wnd = this,
			base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}
		/*
		var url = '/?c=LpuBuildingOffice&m=printList&date=' + Ext.util.Format.date(base_form.findField('date').getValue(), 'd.m.Y');

		if ( !Ext.isEmpty(base_form.findField('LpuBuilding_id').getValue()) ) {
			url += '&LpuBuilding_id=' + base_form.findField('LpuBuilding_id').getValue();
		}

		window.open(url, '_blank');
		*/
		var paramLpu = getGlobalOptions().lpu_id;
		var paramBegDate = Ext.util.Format.date(base_form.findField('date').getValue(), 'd.m.Y');
		var paramLpuBuilding = base_form.findField('LpuBuilding_id').getValue();

		var Report_Params = '&paramLpu=' + paramLpu + '&paramBegDate=' + paramBegDate;

		if ( ! Ext.isEmpty(paramLpuBuilding) ) {
			Report_Params += '&paramLpuBuilding=' + paramLpuBuilding;
		}
		
		printBirt({
			'Report_FileName': 'PrintedForm_ChangeDoctorsRooms.rptdesign',
			'Report_Params': Report_Params,
			'Report_Format': 'pdf'
		});
		return true;
	},
	show: function() {
		sw.Promed.swLpuBuildingOfficePrintWindow.superclass.show.apply(this, arguments);

		var wnd = this,
			base_form = this.FormPanel.getForm();

		base_form.reset();

		base_form.findField('date').setValue(getGlobalOptions().date);

		swLpuBuildingGlobalStore.clearFilter();
		swLpuBuildingGlobalStore.filterBy(function(rec) {
			return (rec.get('Lpu_id') == getGlobalOptions().lpu_id);
		});
		var comboLpuBuilding = base_form.findField('LpuBuilding_id');
		comboLpuBuilding.getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

		comboLpuBuilding.focus(true, 100);
		if(arguments[0].LpuBuilding_id && comboLpuBuilding.findRecord('LpuBuilding_id', arguments[0].LpuBuilding_id)){
			comboLpuBuilding.setValue(arguments[0].LpuBuilding_id);
		}
	},

	/* конструктор */
	initComponent: function() {
		var wnd = this;

		this.FormPanel = new Ext.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			items: [{
				allowBlank: false,
				fieldLabel: 'Дата',
				format: 'd.m.Y',
				name: 'date',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, {
				hiddenName: 'LpuBuilding_id',
				lastQuery: '',
				listWidth: 600,
				width: 320,
				xtype: 'swlpubuildingglobalcombo'
			}],
			labelAlign: 'right',
			labelWidth: 120,
			layout: 'form',
			region: 'north',
			reader: new Ext.data.JsonReader( {
				success: Ext.emptyFn
			}, [
				{ name: 'date' },
				{ name: 'LpuBuilding_id' }
			]),
			url: '/?c=LpuBuildingOffice&m=print'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					wnd.doPrint();
				},
				iconCls: 'save16',
				text: BTN_FRMPRINT
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'close16',
				handler: function() {
					wnd.hide();
				},
				text: BTN_FRMCLOSE
			}],
			items: [
				wnd.FormPanel
			]
		});

		sw.Promed.swLpuBuildingOfficePrintWindow.superclass.initComponent.apply(this, arguments);
	}
});