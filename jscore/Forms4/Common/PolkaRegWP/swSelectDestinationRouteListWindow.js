/**
 * swPacketPrescrCreateWindow - Создание пакета назначений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.PolkaRegWP.swSelectDestinationRouteListWindow', {
	/* свойства */
	alias: 'widget.swSelectDestinationRouteListWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new new-packet-create-window packetWindow dest-route-wnd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	modal: true,
	layout: 'form',
	refId: 'swSelectDestinationRouteListWindow',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Печать маршрутной карты',
	width: 600,
	minWidth: 450,
	height: 350,
	scrollable: 'y',
	autoHeight: true,
	data: {},
	parentPanel: {},
	show: function () {
		if (!arguments || !arguments[0]) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}

		var win = this;
		if (arguments[0].callback) {
			win.callback = arguments[0].callback;
		} else {
			win.callback = Ext6.emptyFn;
		}
		win.Person_id = null;
		if(arguments[0].Person_id)
			win.Person_id = arguments[0].Person_id;
		this.callParent(arguments);
		win.load();
	},
	printDestinationRouteCard: function(Evn_id){
		printBirt({
			'Report_FileName': 'DestinationRouteCard.rptdesign',
			'Report_Params': '&paramEvnPL=' + Evn_id,
			//'Report_Params': '&paramEvnPL=' + this.getView().ownerPanel.EvnPL_id,
			'Report_Format': 'pdf'
		});
		this.hide();
	},
	load: function(){
		var win = this;
		win.EvnVizitWithPrescrGrid.getStore().removeAll();
		if(win.Person_id)
			win.EvnVizitWithPrescrGrid.getStore().load({params: {Person_id: win.Person_id}});
	},
	/* конструктор */
	initComponent: function() {
		var win = this;
		this.EvnVizitWithPrescrGrid = Ext6.create('Ext6.grid.Panel', {
			autoHeight: true,
			xtype: 'grid',
			cls: 'cureStandartsGrid prescrTypeSelGrid',
			viewModel: true,
			buttonAlign: 'center',
			frame: false,
			emptyText: 'Нет результатов.',
			border: false,
			default: {
				border: false
			},
			tbar: {
				padding: '10px 0 10px 30px',
				userCls: 'protocol-diagnoz-message',
				layout: 'fit',
				items: [
					{
						xtype: 'tbtext',
						userCls: 'StandartDiagText',
						itemId: 'StandartDiagText',
						html: '<b>Выберите маршуртную карту назначений для печати:</b>'
					}
				]
			},
			columns: [{
				flex: 1,
				padding: '2 0 7 20',
				margin: '0 30 0 0',
				dataIndex: 'objectSetDate',
				text: '',
				renderer: function(value,el) {
					var resStr = '';
					if(el && el.record){
						resStr += '<div class="rowCureStandart" ><span class="typePrescr" >Посещение за '+value+'</span></div>';
					}
					return resStr;
				},
				userCls: 'cell-without-right-border'
			}],
			plugins: [
				{
					ptype: 'allrowexpander',
					pluginId: 'allrowexpander',
					scrollIntoViewOnExpand: false,
					rowBodyTpl : new Ext6.XTemplate(
						'{[this.formatRow(values)]}',
						{
							formatRow: function (values) {
								var row = 'По данному назначению нет информации';
								if(values){
									row = '<div class="cure-std-row-exp">';
									if(values.Diag_Name)
										row+= '<p><b>Диагноз: </b>'+values.Diag_Code+' '+values.Diag_Name+'</p>';
									if(values.LpuSectionProfile_Name)
										row+= '<p><b>Профиль: </b>'+values.LpuSectionProfile_Name+'</p>';

									row+= '</div>';
								}
								return row;
							}
						})
				}
			],
			store: {
				fields: [
					{name: 'Evn_id', type: 'int'},
					{name: 'Diag_Name', type: 'string'},
					{name: 'Diag_Code', type: 'string'},
					{name: 'objectSetDate', type: 'string'},
					{name: 'objectSetTime', type: 'string'},
					{name: 'LpuSectionProfile_Name', type: 'string'}
				],
				autoLoad: true,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnPrescr&m=loadEvnVizitWithPrescrList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null,
				listeners: {
					load: function(){
						var cmp = win.EvnVizitWithPrescrGrid;
						var allrowexpander =  cmp.getPlugin('allrowexpander');
						allrowexpander.expandAll();
						cmp.getView().focus();
						cmp.getSelectionModel().select(0);
					}
				}
			},
			listeners:{
				itemclick: function (cmp, record, item, index, e, eOpts ) {
					var Evn_id = record.get('Evn_id');
					if(!Ext6.isEmpty(Evn_id))
						win.printDestinationRouteCard(Evn_id);
				}
			}
		});

		Ext6.apply(win, {
			layout: 'fit',
			bodyPadding: 0,
			margin: 0,
			border: false,
			items: [
				win.EvnVizitWithPrescrGrid
			],
			buttons: ['->', {
				handler: function () {
					win.hide();
				},
				cls: 'buttonCancel',
				text: 'Отмена'
			}]
		});

		this.callParent(arguments);
	}
});