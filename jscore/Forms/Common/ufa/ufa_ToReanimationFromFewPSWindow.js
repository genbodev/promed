/**
 * ufa_ToReanimationFromFewPSWindow - окно выбора одной из нескольких "карт выбывшего из стационара" одного пациента в одной ЛПУ
 *                              для операции перевода пациента в Реанимацию 
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Muskat Boris (bob@npk-progress.com)
 * @version			24.03.2017
 * C:\Zend\Promed\jscore\Forms\Common\ufa\ufa_ToReanimationFromFewPSWindow.js
 */

/*NO PARSE JSON*/



sw.Promed.ufa_ToReanimationFromFewPSWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'ufa_ToReanimationFromFewPSWindow',
	width: 580,
	autoHeight: true,
	modal: true,

	action: 'view',
	callback: Ext.emptyFn,

	show: function() {
		console.log('BOB_ufa_ToReanimationFromFewPSWindow_arguments[0]=',arguments[0]);

		sw.Promed.ufa_ToReanimationFromFewPSWindow.superclass.show.apply(this, arguments);
                
        var win = this;
		var form = win.FormPanel.getForm();


        if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
 
		this.Server_id = arguments[0].Server_id;
		this.Person_id = arguments[0].Person_id;
		this.PersonEvn_id = arguments[0].PersonEvn_id;
		this.Status = arguments[0].Status;
		this.EvnPS_id = arguments[0].EvnPS_id;
		this.EvnSection_id = arguments[0].EvnSection_id;
		this.LpuSection_id = arguments[0].LpuSection_id;
		this.MedService_id = arguments[0].MedService_id;

		//BOB - 19.06.2019
		if(this.Status == 'ManyReanimatMedService'){
			win.setTitle('Службы реанимации: перевод в реанимацию');
			this.findById('TRFFPSW_ufa_ToReanimationFromFewPSlbl').setText('в МО несколько медслужб реанимации, выберите нужную');
		}
		else {
			win.setTitle('Карты ВС: перевод в реанимацию');
			this.findById('TRFFPSW_ufa_ToReanimationFromFewPSlbl').setText('имеет несколько карт выбывшего из стационара, выберите нужную');
		}

		this.TRFFPSW_ufa_ToReanimationFromFewPSGrid = new sw.Promed.ViewFrame({
			id: 'TRFFPSW_ufa_ToReanimationFromFewPSGrid',
			dataUrl: '/?c=EvnReanimatPeriod&m=getToReanimationFromFewPS',   //'/?c=EvnSection&m=getToReanimationFromFewPS',
			toolbar: true,
			autoLoadData: false,
			height: 350,
			onDblClick: function() {
				win.doSave();
			},
			stringfields:
				[
					{name: 'EvnPS_NumCard', type: 'string', header: langs('Номер карты'), hidden: (this.Status == 'ManyReanimatMedService')  },
					{name: 'EvnPS_id', type: 'int', hidden: true, key: true},
					{name: 'EvnSection_id', type: 'int', hidden: true, key: true},
					{name: 'LpuSection_id', type: 'int', hidden: true},
					{name: 'EvnPS_setDate', type: 'string', header: langs('Дата начала'), width: 100, hidden: (this.Status == 'ManyReanimatMedService') },
					{name: 'LpuSection_FullName', type: 'string', header: langs((this.Status == 'ManyReanimatMedService') ? 'Медслужба реанимации' : 'Отделение'), id: 'autoexpand'}
				],
			actions:
				[
					{name:'action_add', hidden: true},
					{name:'action_edit', hidden: true},
					{name:'action_view', hidden: true},
					{name:'action_delete', hidden: true},
					{name:'action_refresh', hidden: true},
					{name:'action_print', hidden: true}
				]
		});

        var grid = this.TRFFPSW_ufa_ToReanimationFromFewPSGrid;

		this.findById('TRFFPSW_ufa_ToReanimationFromFewPSGridPanel').removeAll();
		this.findById('TRFFPSW_ufa_ToReanimationFromFewPSGridPanel').add(grid);
		this.findById('TRFFPSW_ufa_ToReanimationFromFewPSGridPanel').doLayout();
		//BOB - 19.06.2019


		grid.loadData({
			params: {
				Server_id: arguments[0].Server_id,
				Person_id: arguments[0].Person_id,
				PersonEvn_id: arguments[0].PersonEvn_id,
				Lpu_id: arguments[0].Lpu_id,
				MedService_id: arguments[0].MedService_id, //BOB - 02.10.2019
				Status: arguments[0].Status
			} ,
			globalFilters: {
				Server_id: arguments[0].Server_id,
				Person_id: arguments[0].Person_id,
				PersonEvn_id: arguments[0].PersonEvn_id,
				Lpu_id: arguments[0].Lpu_id,
				MedService_id: arguments[0].MedService_id, //BOB - 02.10.2019
				Status: arguments[0].Status
			}
		});
          
		this.TRFFPSW_ufa_ToReanimationFromFewPSPersonInfo.load({
			   Person_id: arguments[0].Person_id
		});
	},
        
    doSave: function() {
            
		var selected = this.TRFFPSW_ufa_ToReanimationFromFewPSGrid.getGrid().getSelectionModel().getSelected();
		var pdata = { Server_id: this.Server_id,
			Person_id: this.Person_id,
			PersonEvn_id: this.PersonEvn_id,
			Status: 'FromManyEvnPS'
		};

		if(this.Status == 'ManyReanimatMedService'){
			pdata.EvnPS_id = this.EvnPS_id;
			pdata.EvnSection_id = this.EvnSection_id;
			pdata.LpuSection_id = this.LpuSection_id;
			pdata.MedService_id = selected.get('EvnPS_id');
		}
		else {
			pdata.EvnPS_id = selected.get('EvnPS_id');
			pdata.EvnSection_id = selected.get('EvnSection_id');
			pdata.LpuSection_id = selected.get('LpuSection_id');
			pdata.MedService_id = this.MedService_id;
		}
		//    console.log('BOB_Object_pdata=',pdata);  //BOB - 17.03.2017
        this.callback(pdata);
            

    },

	initComponent: function() {
            
        var win = this;

  
		this.TRFFPSW_ufa_ToReanimationFromFewPSPersonInfo = new sw.Promed.PersonInformationPanelShort({
			region: 'north',
			id:'TRFFPSW_ufa_ToReanimationFromFewPSPersonInfo'
		});

		var varLabel = new Ext.form.Label({
			id: 'TRFFPSW_ufa_ToReanimationFromFewPSlbl',
			text: 'имеет несколько карт выбывшего из стационара, выберите нужную'
		});
  
		//СОЗДАНИЕ СВОЙСТВА ФОРМЫ - ОБЪЕКТ СПИСОК карт выбывшего из стационара


		this.FormPanel = new Ext.form.FormPanel({
			buttonAlign: 'left',
			frame: true,
			id: 'TRFFPSW_ufa_ToReanimationFromFewPSForm',
			labelAlign: 'right',

			items: [{
					xtype: 'hidden',
					value: 0,
					name: 'RecordStatus_Code'
				},
				win.TRFFPSW_ufa_ToReanimationFromFewPSPersonInfo,
				varLabel,
				//BOB - 19.06.2019
				{
					xtype: 'panel',
					frame: false,
					border: false,
					autoScroll: true,
					//width: Ext.getBody().getWidth() - 680,
					height: 352,
					id: 'TRFFPSW_ufa_ToReanimationFromFewPSGridPanel',
					layout: 'column',
					items: [

					]
				}
				//BOB - 19.06.2019

            ]
		});
            
            
		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: langs('Выбрать'),
					id: 'TRFFPSW_ButtonSave',
					tooltip: langs('Выбрать'),
					iconCls: 'save16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'TRFFPSW_CancelButton',
					text: langs('Отменить')
				}
			]
		});
            
        sw.Promed.ufa_ToReanimationFromFewPSWindow.superclass.initComponent.apply(this, arguments);

	}



});

