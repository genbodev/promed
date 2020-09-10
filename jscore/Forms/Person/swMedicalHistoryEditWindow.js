/**
 * swMedicalHistoryEditWindow - окно.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @version      08.08.2011
 */
sw.Promed.swMedicalHistoryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	title:"Оригинал ИБ",
	width: 700,
	minHeight:435,
	height:435,
	maxHeight:400,
	id: 'swMedicalHistoryEditWindow',
	listeners: {
		hide: function () {
			this.onHide();
		}
	},
	layout: 'border',
	maximizable: true,
	minHeight: 435,
	minWidth: 700,
	modal: false,
	show: function () {
		sw.Promed.swMedicalHistoryEditWindow.superclass.show.apply(this, arguments);
		var that = this;
		this.title = "Оригинал ИБ"; 
		if (!arguments[0]|| !arguments[0].EvnPS_id) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: 'Ошибка открытия формы.<br/>Не указаны нужные входные параметры.',
					title: 'Ошибка',
					fn: function () {
						this.hide();
					}
				});
		}
		this.focus();
		this.callback =Ext.emptyFn;
		if (arguments[0].EvnPS_id) {
			this.EvnPS_id = arguments[0].EvnPS_id;
		} 
		if (arguments[0].action) {
			this.action = arguments[0].action;
		} else {
			if (( this.EvnPS_id ) && ( this.EvnPS_id > 0 ))
				this.action = "edit";
			else
				this.action = "add";
		}
		
		var form = this.findById('MedicalHistoryEditForm');
		form.getForm().reset();
		var grid = this.EvnPSLocatGrid.getGrid();
		grid.getStore().removeAll();
		
		form.getForm().setValues(arguments[0]);
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) {
			case 'add':
			
				break;
			case 'edit':
				
				this.findById('MedicalHistoryEditForm').getForm().reset();
				this.setTitle(that.title + ': Редактирование');
				break;
			case 'view':
				this.findById('MedicalHistoryEditForm').getForm().reset();
				this.setTitle(that.title + ': Просмотр');
				this.enableEdit(false);
				break;
		}
		if (this.action != 'add') {
			form.getForm().load(
				{
					params: {
						EvnPS_id: that.EvnPS_id
					},
					failure: function () {
						loadMask.hide();
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function () {
								that.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
							title: 'Ошибка'
						});
					},
					success: function () {
						
						loadMask.hide();
						var lpu_building = form.getForm().findField('LpuBuilding_id')
						lpu_building.getStore().load({
							callback:function(){
								lpu_building.setValue(lpu_building.getValue())
							}
						})
					},
					url: '/?c=EvnPSLocat&m=loadMedicalHistory'
				});
				that.reloadEvnPSLocatGrid();
		}
	
		var grid = this.findById('EvnPSLocatGrid');
		if (this.action != 'view') {
			grid.setReadOnly(false);
		} else {
			
			//this.buttons[2].focus();
			grid.setReadOnly(true);
		}
	},
	
	reloadEvnPSLocatGrid:function(){
	    var win = this;
	    var grid = this.EvnPSLocatGrid.getGrid();
	    var params = {EvnPS_id:win.EvnPS_id}
		params.start = 0;
	    params.limit = 5;
	    var baseParams = params;
	    grid.getStore().removeAll();
	    grid.getStore().baseParams = baseParams;
	    
	   this.EvnPSLocatGrid.loadData({globalFilters: params});
		
	},
	openAmbulatCardLocatWindow:function(action){
	    var  params = {};
	    var grid = this.findById('EvnPSLocatGrid').getGrid();
	    var win = this;
		params.callback= function (data) {
			win.reloadEvnPSLocatGrid();
		}
	    if(action=='add'){
		    params.formParams = {
				EvnPS_id:win.EvnPS_id
		    };
	    }else{
		var record = grid.getSelectionModel().getSelected();
			if(record.get('EvnPSLocat_id')>0){
			params.formParams = {
				EvnPSLocat_id:record.get('EvnPSLocat_id')
				}
			}else{
				params.formParams=record.data;
			}
			
	    }
		
	    params.formParams.Server_id = win.form.findField('Server_id').getValue();
	    params.action = action;
	    getWnd('swEvnPSLocatEditWindow').show(params);
	},
	initComponent: function () {
		var that = this;
		this.EvnPSLocatGrid= new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add',
					handler:function(){that.openAmbulatCardLocatWindow('add')}
				    },
				{
					name: 'action_edit',
					handler:function(){that.openAmbulatCardLocatWindow('edit')}
				    },
				{name: 'action_view',
					handler:function(){that.openAmbulatCardLocatWindow('view')}
				    },
				{name: 'action_refresh',hidden: true, disabled: true},
				{name: 'action_delete'},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=EvnPSLocat&m=getEvnPSLocatList',
			region: 'center',
			object: 'PersonEvnPSLocat',
			editformclassname: 'swEvnPSLocatEditWindow',
			id: 'EvnPSLocatGrid',
			paging: true,
			root: 'data',
			pageSize:5,
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'PersonEvnPSLocat_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonEvnPSLocat_begDate', type: 'string', header: 'Дата и время движения', id: 'autoexpand'},
				{name: 'AmbulatCardLocatType', type: 'string', header: 'Местонахождение'},
				{name: 'FIO', type: 'string', header: 'ФИО сотрудника'},
				{name: 'MedStaffFact', type: 'string', header: 'Должность сотрудника'},
				{name: 'PersonEvnPSLocat_Desc', type: 'string', header: 'Коментарий к движению'},
				{name:'MedPersonal_id',type:'int',hidden:true},
				{name:'MedStaffFact_id',type:'int',hidden:true},
				{name:'AmbulatCardLocatType_id',type:'int',hidden:true},
				{name:'Server_id',type:'int',hidden:true},
				{name:'PersonEvnPSLocat_OtherLocat',type:'int',hidden:true},
				{name:'PersonEvnPSLocat_begT',type:'string',hidden:true},
				{name:'PersonEvnPSLocat_begD',type:'date',hidden:true},
				{name:'isSave',type:'int',hidden:true}
			],
			title: 'Движения оригинала ИБ',
			toolbar: true
		});
		this.MedicalHistoryEditForm = new Ext.form.FormPanel({
			autoHeight: true,
			border: false,
			buttonAlign: 'left',
			frame: false,
			region: 'north',
			id: 'MedicalHistoryEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			items: [
				{
					id: 'EvnPS_id',
					name: 'EvnPS_id',
					value: 0,
					xtype: 'hidden'
				},{
					name: 'Person_id',
					xtype: 'hidden'
				},{
					name: 'Server_id',
					xtype: 'hidden'
				},
				new Ext.Panel({
					//height: Ext.isIE ? 310 : 295,
					id: 'MedicalHistoryEditFormFieldPanel',
					bodyStyle: 'padding-top: 0.2em;',
					border: false,
					frame: false,
					style: 'margin-bottom: 0.1em;',
					items: [
						{
							disabled:true,
							name: 'EvnPS_NumCard',
							fieldLabel: 'Номер ИБ',
							hiddenName:'EvnPS_NumCard',
							xtype: 'textfield'
						},
						{
						    allowBlank: false,
						    fieldLabel: 'Подразделение МО',
						    xtype:'swlpubuildingcombo',
							disabled:true,
						    width: 200,
						    name: 'LpuBuilding_id',
						    hiddenName:'LpuBuilding_id'
						},{
						    fieldLabel: 'Пациент',
						    xtype:'textfield',
						    hiddenName:'PersonFIO',
						    name:'PersonFIO',
						    disabled:true
						    
						}
						
					],
					layout: 'form'
				})
			],
			keys: [
				{
					alt: true,
					fn: function (inp, e) {
						switch (e.getKey()) {
							case Ext.EventObject.C:
								if (this.action != 'view') {
									this.doSave(false);
								}
								break;
							case Ext.EventObject.J:
								this.hide();
								break;
						}
					},
					key: [ Ext.EventObject.C, Ext.EventObject.J ],
					scope: this,
					stopEvent: true
				}
			],
			reader: new Ext.data.JsonReader({
				success: function () {
					//
				}
			}, [
				{ name: 'EvnPS_id' },
				{ name: 'Person_id' },
				{ name: 'Server_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'PersonFIO' },
				{ name: 'EvnPS_NumCard' }
			])
		});
		Ext.apply(this, {
			buttons: [
				
				{
					text: '-'
				},
				{
					handler: function () {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: "Закрыть",
					onTabAction: function () {
						if (that.action == 'view') {
							that.focusOnGrid();
						}
					},
					onShiftTabAction: function () {
						if (that.action == 'view') {
							that.focusOnGrid();
						} else {
							that.buttons[0].focus();
						}
					}
				}
			],
			items: [
				this.MedicalHistoryEditForm,
				this.EvnPSLocatGrid
			]
		});
		sw.Promed.swMedicalHistoryEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById("MedicalHistoryEditForm").getForm();
		this.focusOnGrid = function () {
			var grid = that.findById('EvnPSLocatGrid').getGrid();
			if (grid.getStore().getCount() > 0) {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}

	}
});