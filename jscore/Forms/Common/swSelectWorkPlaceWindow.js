/**
* swSelectWorkPlaceWindow - окно выбора места работы врача, в случае если у врача их несколько
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       private
* @class 		sw.Promed.swSelectWorkPlaceWindow
* @extends 		sw.Promed.BaseForm
* @copyright    Copyright (c) 2009 Swan Ltd.
*/
/*NO PARSE JSON*/

sw.Promed.swSelectWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	id: 'swSelectWorkPlaceWindow',
	initComponent: function() {
	
		this.grid = new Ext.grid.GridPanel({
			height: 200,
			width: 586,
			id: 'selectWorkPlaceGrid',
			region: 'center',
			/*autoExpandColumn: 'autoExpand',
			viewConfig: 
			{
				forceFit: true,
				autoFill: true
			},*/
			columns:[{
				dataIndex: 'ARMNameLpu',
				header: lang['arm_lpu'],
				resizable: false,
				sortable: false,
				width: 200
			}, {
				dataIndex: 'Name',
				header: '<font color="#000">Подразделение / Отделение</font> / <font color="darkblue">Служба</font>',
				resizable: false,
				sortable: false,
				width: 240
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
				width: 64
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
				dataIndex: 'MedService_id',
				hidden: true
			}, {
				dataIndex: 'MedService_Name',
				hidden: true
			}, {
				dataIndex: 'MedServiceType_SysNick',
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

		sw.Promed.swSelectWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
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
	/**
	 * Функция установки места работы врача по умолчанию.
	 * Записывает данные в локальные настройки пользователя (LDAP)
	 * Сохраняемые значения: название арма, тип арма, врач, отделение, служба
	 */
	setDefaultWorkPlace: function(record) {
		var form = this;
		this.getLoadMask().show();
		Ext.Ajax.request({
			url: '/?c=User&m=setDefaultWorkPlace',
			params: record,
			callback: function(options, success, response) {
				this.getLoadMask().hide();
				if (success) {
					// Думаем что все сохранилось в настройках
					var result = Ext.util.JSON.decode(response.responseText);
					// Устанавливаем правильные глобальные переменные 
					Ext.globalOptions.defaultARM = result;
					sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: 'common',
					onSelect: null
				});
					this.hide();
				}
			}.createDelegate(this)
		});
	},
	onSelect: function() {
		var sm = this.grid.getSelectionModel();
		if ( sm.hasSelection() ) {
			var record = sm.getSelected();
			/*
			if ( !record || !record.get('MedStaffFact_id') ) {
				return false;
			}
			*/
			// Установить выбранное место работы по умолчанию 
			this.setDefaultWorkPlace(record.data);
		}
		else {
			sw.swMsg.alert(lang['oshibka'], lang['vyiberite_neobhodimoe_mesto_rabotyi_dlya_togo_chtobyi_ustanovit_ego_osnovnyim_pri_vhode_v_arm']);
		}
	},
	/*
	setFocus: function() {
		if (this.grid.getStore().getCount()>0) {
			this.grid.getView().focusRow(0);
			this.grid.getSelectionModel().selectFirstRow();
		}
	},
	*/
	show: function() {
		sw.Promed.swSelectWorkPlaceWindow.superclass.show.apply(this, arguments);
		//this.setFocus();
		// TODO: Выбрать первую строку в гриде или если уже есть выбранное (в настройках) уставить 
		
	},
	title: lang['vyiberite_mesto_rabotyi_arm_po_umolchaniyu'],
	width: 720,
	height: 300
});
