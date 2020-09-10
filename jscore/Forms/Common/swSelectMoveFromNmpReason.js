/**
* swSelectMoveFromNmpReason - форма выбора причины возврата из ПДД в СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Miyusov Alexandr
* @version      19.02.2013
*/

sw.Promed.swSelectMoveFromNmpReason = Ext.extend(sw.Promed.BaseForm, {
	width: 700,
	height: 700,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	callback: Ext.emptyFn,
	listeners: {
		hide: function() {
			this.GridPanel.ViewGridPanel.getStore().removeAll();
		}			
	},
	
	show: function(params) {
		var me = this;

        sw.Promed.swSelectMoveFromNmpReason.superclass.show.apply(this, arguments);

        if (!params.toSmp) {
        	me.form.setVisible(false);
		} else {
        	me.toSmp = true;
		}

        //выбрали МО - загружаем подстанции
		me.lpuCombo.on('select', function (cmp, rec) {
			me.lpuBuildingCombo.store.load({
				params: {
					Lpu_id: rec.data.Lpu_id
				}
			})
		});

		//если определилась МО - грузим подстанции и подставляем определенную или первую попавшуюся
		if (params.Lpu_id) {
			me.lpuCombo.setValue(params.Lpu_id);
			me.lpuBuildingCombo.store.load({
				params: {
					Lpu_id: params.Lpu_id
				}
			});
			
			me.lpuBuildingCombo.store.on('load', function (opts, data) {
				//если загрузилось не то что надо (кривая загрузка при первом раскрытии)
				if (data[0] && (data[0].data.Lpu_id != me.lpuCombo.getValue()) && me.lpuCombo.getValue()) {
					me.lpuBuildingCombo.store.load({
						params: {
							Lpu_id: me.lpuCombo.getValue()
						}
					})
				}
				
				if (params.LpuBuilding_id && (me.lpuCombo.getValue() == params.Lpu_id)) {
					me.lpuBuildingCombo.setValue(params.LpuBuilding_id)
				} else {
					me.lpuBuildingCombo.setValue(data[0]? data[0].data.LpuBuilding_id: null);
				}
			})
		}
		if ( !arguments[0]) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		this.setTitle(lang['vyiberite_prichinu_peredachi']);
		
		with(this.GridPanel.ViewGridPanel.getStore()) {
			load();
		}
	
		this.center();
	},
	
	selectResult: function() {
		var me = this,
			form = me.form,
			record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();

		if (!form.getForm().isValid() && me.toSmp) {
			Ext.Msg.alert('Ошибка', 'не выбрана МО передачи и подстанция СМП');
			return false;
		}

		if(!record) return false;
		var parentObject = this;
		if (record.data.requiredTextField == 1) {
			Ext.Msg.prompt(lang['vvedite_prichinu_peredachi'], lang['pojaluysta_vvedite_prichinu_peredachi'], function(btn, text){
				if (btn == 'ok'){
					text = text.replace(/^\s+/, "").replace(/\s+$/, "");
					if (text=="") {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								parentObject.selectResult();
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: lang['vvedite_prichinu_peredachi'],
							title: lang['pojaluysta_vvedite_prichinu_peredachi']
						});
					}
					else {
						record.data.comment = text;
						record.data.Lpu_id = me.lpuCombo.getValue();
						record.data.LpuBuilding_id = me.lpuBuildingCombo.getValue();
						parentObject.callback(record.data);
						parentObject.hide();
					}
				}
				else {
					parentObject.GridPanel.focus();
				}

			}, '', 60)
		}
		else {
			record.data.Lpu_id = me.lpuCombo.getValue();
			record.data.LpuBuilding_id = me.lpuBuildingCombo.getValue();
			record.data.comment = null;
			this.callback(record.data);
			this.hide();
		}
	},
	
	initComponent: function() {
		var me = this;

		this.height = 400;
		this.width = 400;
		this.GridPanel = new sw.Promed.ViewFrame({					
			id: this.id + '_Grid',
			toolbar: false,
			onEnter: this.selectResult.createDelegate(this),
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			//pageSize: 20,
			//paging: true,
			border: false,
			autoLoadData: false,
			stripeRows: true,
			//root: 'data',
			stringfields: [
				{name: 'CmpMoveFromNmpReason_id', type: 'int', hidden: true, key: true},
				{name: 'requiredTextField', type: 'int', hidden: true},
				{name: 'CmpMoveFromNmpReason_Name', header: lang['prichina'], type: 'string', id: 'autoexpand'}
			],
			dataUrl: '/?c=CmpCallCard&m=getMoveFromNmpReasons',
			totalProperty: 'totalCount'
		});

		me.lpuCombo = new sw.Promed.SwLpuCombo({
			fieldLabel: 'МО передачи СМП',
			labelWidth: 110,
			labelAlign: 'top',
			allowBlank: false
		});

		me.lpuBuildingCombo = new sw.Promed.SmpUnits({
			allowBlank: false
		});

		me.form = new Ext.form.FormPanel({
			autoHeight: true,
			items: [
				me.lpuCombo,
				me.lpuBuildingCombo
			]
		});

		this.GridPanel.ViewGridPanel.on('rowdblclick', this.selectResult.createDelegate(this));
		this.GridPanel.ViewGridPanel.on('render', function() {
			this.GridPanel.ViewContextMenu = new Ext.menu.Menu();
		}.createDelegate(this));
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.selectResult.createDelegate(this)
			}, 
			'-',
			{
				text: lang['zakryit'],
				iconCls: 'close16',
				handler: function(button, event) {
					button.ownerCt.hide();
				}
			}],
			items: [
				me.form,
				this.GridPanel
			]

		});
		
		sw.Promed.swSelectMoveFromNmpReason.superclass.initComponent.apply(this, arguments);
	}
});