/**
* swSelectMedStaffFactWindow - окно выбора места работы врача, в случае если у врача их несколько
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       private
* @class 		sw.Promed.swSelectMedStaffFactWindow
* @extends 		sw.Promed.BaseForm
* @copyright    Copyright (c) 2009 Swan Ltd.
*/
/*NO PARSE JSON*/

sw.Promed.swSelectMedStaffFactWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swSelectMedStaffFactWindow',
	objectSrc: '/jscore/Forms/Common/swSelectMedStaffFactWindow.js',

	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	id: 'SelectMedStaffFactWindow',
	initComponent: function() {
		this.grid = new Ext.grid.GridPanel({
			height: 200,
			width: 586,
			id: 'selectMedStaffFactGrid',
			region: 'center',
			/*autoExpandColumn: 'autoExpand',
			viewConfig: 
			{
				forceFit: true,
				autoFill: true
			},*/
			columns:[{
				dataIndex: 'LpuSection_Name',
				header: lang['otdelenie'],
				resizable: false,
				sortable: false,
				width: 180
			}, {
				dataIndex: 'LpuUnit_Name',
				header: lang['podrazdelenie'],
				//id: 'autoExpand',
				resizable: false,
				sortable: false,
				width: 144
			}, {
				dataIndex: 'PostMed_Name',
				header: lang['doljnost'],
				resizable: false,
				sortable: false,
				width: 180
			}, {
				dataIndex: 'Timetable_isExists',
				header: lang['raspisanie'],
				resizable: false,
				renderer: sw.Promed.Format.checkColumn,
				sortable: false,
				width: 80
			}, {
				dataIndex: 'MedStaffFact_id',
				hidden: true
			}, {
				dataIndex: 'LpuSection_id',
				hidden: true
			}, {
				dataIndex: 'MedPersonal_id',
				hidden: true
			}, {
				dataIndex: 'PostMed_Code',
				hidden: true
			}, {
				dataIndex: 'PostMed_id',
				hidden: true
			}, {
				dataIndex: 'LpuUnitType_id',
				hidden: true
			}],
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					this.onSelect();
				}.createDelegate(this)
			},
			store: sw.Promed.MedStaffFactByUser.store
		});

		Ext.apply(this, {
			buttons: [{
			    text: lang['vyibrat'],
			    handler: function(){
					this.onSelect();
			    }.createDelegate(this),
			    iconCls: 'checked16'
			}, {
				text: '-'
			}, {
				handler: function()  {
					this.hide()
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			defaults: {
				split: true
			},
			layout: 'border',
			items: [
				this.grid
			]
		});

		sw.Promed.swSelectMedStaffFactWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		key: [ Ext.EventObject.ENTER ],
		fn: function() {
			Ext.getCmp('SelectMedStaffFactWindow').onSelect();
		}
	}],
	listeners: {
		'hide': function() {
			//this.grid.getStore().removeAll();
		}
	},
	modal: true,
	onSelect: function() {
		var sm = this.grid.getSelectionModel();

		if ( sm.hasSelection() ) {
			var record = sm.getSelected();
			if ( !record || !record.get('MedStaffFact_id') ) {
				return false;
			}
			var usermsf_obj = sw.Promed.MedStaffFactByUser;
			usermsf_obj.setMedStaffFact({
				MedStaffFact_id: record.get('MedStaffFact_id'),
				ARMType: usermsf_obj.ARMType,
				onSelect: usermsf_obj.onSelect
			});
			this.hide();
		}
		else {
			sw.swMsg.alert(lang['oshibka'], lang['vyiberite_stroku_v_tablitse']);
		}
	},
	show: function() {

		sw.Promed.swSelectMedStaffFactWindow.superclass.show.apply(this, arguments);

	},
	title: lang['vyiberite_mesto_rabotyi'],
	width: 600,
	height: 300
});
