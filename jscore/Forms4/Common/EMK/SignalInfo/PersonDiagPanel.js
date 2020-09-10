/**
 * Панель списка уточненных диагнозов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.SignalInfo.PersonDiagPanel', {
	extend: 'swPanel',
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	allTimeExpandable: false,
	openDiagSpecEditWindow: function() {
		var form = this;
		var formParams = new Object();

		formParams.EvnDiagSpec_id = 0;
		formParams.Person_id = form.Person_id;
		formParams.Server_id = form.Server_id;
		var my_params = new Object({
			EvnDiagSpec_id: formParams.EvnDiagSpec_id,
			formParams: formParams
		});
		form.openForm('swDiagSpecEditWindow',my_params,langs('Добавление уточненного диагноза'));	
	},
	openForm: function (open_form, oparams, title) {
		var me = this;
		
		if (getWnd(open_form).isVisible()) {
			if (open_form == 'swDirectionMasterWindow') {
				Ext6.Msg.alert(langs('Сообщение'), langs('Форма ')+' '+ ((title)?title:open_form) +' '+langs(' в данный момент открыта.'));
			}
			return false;
		} else {
			var params = {
				action: 'add',
				PersonEvn_id: me.PersonEvn_id,
				Person_id: me.Person_id,
				Server_id: me.Server_id,
				UserMedStaffFact_id: me.userMedStaffFact.MedStaffFact_id || null,
				UserLpuSection_id: me.userMedStaffFact.LpuSection_id || null,
				userMedStaffFact: me.userMedStaffFact || null
			}; 
			params.personData = {
				PersonEvn_id: me.PersonEvn_id,
				Person_id: me.Person_id,
				Server_id: me.Server_id
			};
			params.callback = function() {
				me.load();	
			};
			Object.assign(params, oparams)

			getWnd(open_form).show(params);
		}
	},
	onBtnAddClick: function(){
		this.openDiagSpecEditWindow();
	},
	title: 'СПИСОК УТОЧНЕННЫХ ДИАГНОЗОВ',
	collapsed: true,
	setParams: function(params) {
		var me = this;

		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.userMedStaffFact = params.userMedStaffFact;
		me.PersonEvn_id = params.PersonEvn_id;
		me.loaded = false;

		if (!me.collapsed) {
			me.load();
		}
	},
	loaded: false,
	listeners: {
		'expand': function() {
			if (!this.loaded) {
				this.load();
			}
		}
	},
	load: function() {
		var me = this;
		this.loaded = true;
		this.EvnDiagGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	initComponent: function() {
		var me = this;

		this.EvnDiagGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'EmkGrid',
			padding: 10,
			columns: [{
				width: 120,
				header: 'Дата',
				dataIndex: 'Diag_setDate'
			}, {
				width: 200,
				header: 'МО',
				dataIndex: 'Lpu_Nick'
			}, {
				width: 120,
				flex: 1,
				header: 'Диагноз',
				dataIndex: 'Diag_Name',
				renderer: function (value, metaData, record) {
					var s = record.get('Diag_Name');
					if (record.get('Diag_Code')) {
						s = record.get('Diag_Code') + ' ' + s;
					}
					return s;
				}
			}, {
				width: 120,
				header: 'Профиль',
				dataIndex: 'LpuSectionProfile_Name'
			}, {
				width: 200,
				header: 'Врач',
				dataIndex: 'MedPersonal_Fio'
			}, {
				width: 40,
				dataIndex: 'EvnDiag_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnDiag_id', type: 'int' },
					{ name: 'Diag_setDate', type: 'string' },
					{ name: 'Lpu_Nick', type: 'string' },
					{ name: 'Diag_Code', type: 'string' },
					{ name: 'Diag_Name', type: 'string' },
					{ name: 'MedPersonal_Fio', type: 'string' },
					{ name: 'LpuSectionProfile_Name', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						if(records)
							me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnDiag&m=loadPersonDiagPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'EvnDiag_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnDiagGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					this.openDiagSpecEditWindow();
				}
			}]
		});

		this.callParent(arguments);
	}
});