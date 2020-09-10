/**
* swPalliatPPSScaleSelectWindow - Шкала PPS
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @comment      
*/

/*NO PARSE JSON*/

sw.Promed.swPalliatPPSScaleSelectWindow = Ext.extend(sw.Promed.BaseForm,
{
	autoHeight: true,
	objectName: 'swPalliatPPSScaleSelectWindow',
	objectSrc: '/jscore/Forms/Common/swPalliatPPSScaleSelectWindow.js',
	title:langs('Шкала PPS'),
	layout: 'border',
	modal: true,
	shim: false,
	resizable: false,
	maximizable: false,
	listeners: {
		hide: function()
		{
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	width: 900,
	show: function() {
		sw.Promed.swPalliatPPSScaleSelectWindow.superclass.show.apply(this, arguments);
		this.callback = arguments[0].callback || Ext.emptyFn;
        this.ViewFrame.loadData();
	},
	submit: function() {
		var record = this.ViewFrame.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('PalliatPPSScale_id')) return false;
		
		this.callback(record.get('PalliatPPSScale_id'));
		this.hide();
	},	
	initComponent: function()
	{
		var form = this;
		
		this.ViewFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			height: 300,
			autoLoadData: false,
			dataUrl: '/?c=PalliatQuestion&m=loadPalliatPPSScale',
			object: 'PalliatQuestion',
			toolbar: false,
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', handler: function(){form.submit();}},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh', hidden: true, disabled: true},
				{name:'action_print', hidden: true, disabled: true}
			],
			singleSelect:true,
			paging: false,
			region: 'center',
			stringfields: [
				{name: 'PalliatPPSScale_id', type: 'int', hidden: true, key: true},
                {name: 'PalliatPPSScale_Percent', header: 'Оценка в %', type: 'string', width: 80},
                {name: 'PalliatPPSScale_MoveAbility', header: 'Способность к передвижению', type: 'string', id: 'autoexpand'},
                {name: 'PalliatPPSScale_ActivityType', header: 'Виды активности и проявления болезни ', type: 'string', width: 150},
                {name: 'PalliatPPSScale_SelfCare', header: 'Самообслуживание', type: 'string', width: 150},
                {name: 'PalliatPPSScale_Diet', header: 'Питание/питье', type: 'string', width: 150},
                {name: 'PalliatPPSScale_ConsiousLevel', header: 'Уровень сознания', type: 'string', width: 150},
			],
		});
		
		Ext.apply(this,
		{
			region: 'center',
			layout: 'form',
			buttons:
			[{
				text: langs('Выбрать'),
				id: 'lsqefOk',
				iconCls: 'ok16',
				handler: function() {
					form.submit();
				}
			},{
				text: '-'
			}, 
			//HelpButton(this, -1), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() {this.hide();}.createDelegate(this)
			}],
			items: [
				form.ViewFrame
			]
			
		});
		sw.Promed.swPalliatPPSScaleSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});
