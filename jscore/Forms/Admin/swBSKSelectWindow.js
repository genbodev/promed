/**
 * swBSKSelectWindow - окно выбора предмета наблюдения регистра БСК
 * 
 */

sw.Promed.swBSKSelectWindow = Ext.extend(sw.Promed.BaseForm, {
    id: 'choiceBSKObjectWindow',
    MorbusType_id: false,
    modal: true,
    title: langs('Выбор предмета наблюдения регистра БСК'),
    height: 120,
    width: 330,
	closable      : false,	
    closeAction: 'hide',
    bodyStyle: 'padding:10px;border:0px;',
	lastSelectedMorbusType_id: false,
    initComponent: function () {
        Ext.apply(this,
                {
                    items: [
                        {
                            allowBlank: false,
                            anyparam: 'anyparam',
                            id: 'BSKObjectCombo',
                            fieldLabel: '',
                            hideLabel: true,
                            anchor: '100%',
                            mode: 'local',
                            store: new Ext.data.JsonStore({
                                url: '/?c=BSK_Register_User&m=getBSKObjects',
                                autoLoad: false,								
                                fields: [
                                    {name: 'MorbusType_id', type: 'int'},
                                    {name: 'MorbusType_name', type: 'string'}
                                ],
                                key: 'MorbusType_id',
                            }),
                            editable: false,
                            triggerAction: 'all',
                            displayField: 'MorbusType_name',
                            valueField: 'MorbusType_id',
                            width: 300,
                            hiddenName: 'MorbusType_id',
                            tabIndex: 1220022,
                            xtype: 'combo',
                            listeners: {
                                specialkey: function (field, e) {
                                    console.log('FIELD', field)
                                    if (e.getKey() == e.ENTER) {
                                        Ext.getCmp('getMorbusType_id').handler();
                                    }
                                }
                            }

                        }
                    ], buttons:
                            [
                                {
                                    text: langs('Выбрать'),
                                    id: 'getMorbusType_id',
                                    handler: function () {


                                        var MorbusType_id = Ext.getCmp('BSKObjectCombo').getValue();
										Ext.getCmp('choiceBSKObjectWindow').lastSelectedMorbusType_id = MorbusType_id;;

                                        if (typeof MorbusType_id == 'string') {
                                            sw.swMsg.show(
                                                    {
                                                        icon: Ext.MessageBox.ERROR,
                                                        title: langs('Ошибка'),
                                                        msg: langs('Необходимо выбрать предмет наблюдения!'),
                                                        buttons: Ext.Msg.OK
                                                    });
                                            return false;
                                        }
										if (Ext.getCmp('choiceBSKObjectWindow').inp == true) {
  											Ext.getCmp('choiceBSKObjectWindow').addMorbusType(MorbusType_id);
										} else {
											sw.Promed.personRegister.add({
												MorbusType_id: MorbusType_id,
												//Костыль!!!!!!!!
												MorbusType_SysNick: 'MorbusType_SysNick',
												PersonRegisterType_SysNick: 'PersonRegisterType_SysNick',
												PersonRegister_id: Ext.getCmp('BSKObjectCombo').getValue(),
												PersonRegister_setDate: new Date(),
												Diag_name: 'Diag_name',
												person: (typeof Ext.getCmp('choiceBSKObjectWindow').person != 'undefined') ? Ext.getCmp('choiceBSKObjectWindow').person : undefined,
												callback: function (data) {
												}
											});											
										}
                                        Ext.getCmp('BSKObjectCombo').setValue('');
										Ext.getCmp('choiceBSKObjectWindow').refresh();
									},
                                    style: 'margin-right:150px'
                                },
                                {
                                    text: langs('Отмена'),
									id: 'cansel',
                                    handler: function () {
                                        Ext.getCmp('choiceBSKObjectWindow').MorbusType_id = false;
										Ext.getCmp('choiceBSKObjectWindow').refresh();
                                    }
                                }
                            ],
                }
        );
										
        sw.Promed.swBSKSelectWindow.superclass.initComponent.apply(this, arguments);
		Ext.getCmp('BSKObjectCombo').getStore().on('load', function() {
			//Ext.getCmp('BSKObjectCombo').getStore().removeAt(0); //Убираем из списка ОКС
			var Store = Ext.getCmp('BSKObjectCombo').getStore();
			Store.removeAt(Store.indexOfId(19)); //Убираем из списка ОКС
			Store.removeAt(Store.indexOfId(110)); //Убираем из списка ХСН
			Store.removeAt(Store.indexOfId(111)); //Убираем из списка Приобретённые пороки сердца
			Store.removeAt(Store.indexOfId(112)); //Убираем из списка Врождённые пороки сердца
		});
  },
  addMorbusType : function(MorbusType_id) {
	if (Ext.getCmp('personBskRegistryDataWindow')) var Person_id = Ext.getCmp('personBskRegistryDataWindow').findById('PBRW_infoPacient').getFieldValue('Person_id');
	else var Person_id = Ext.getCmp('personBskRegistryWindow').personInfo.Person_id;
	Ext.Ajax.request({
		url: '?c=BSK_RegisterData&m=saveInPersonRegister',
		params: {
			//Person_id   : Ext.getCmp('personBskRegistryWindow').personInfo.Person_id
			MedPersonal_iid: getGlobalOptions().medpersonal_id,
			PersonRegister_id: null,
			Mode:null,
			Person_id: Person_id,
			PersonEvn_id: null,
			MorbusType_id: MorbusType_id,
			Morbus_id: null,
			Lpu_iid:getGlobalOptions().lpu_id,
			PersonRegister_setDate: (new Date()).dateFormat("d.m.Y"),
			Diag_id: null,
			EvnNotifyBase_id: null,
			PersonRegister_disDate: null,
			PersonRegisterOutCause_id: null,
			PersonRegister_Code: null,
			Lpu_did : null,
			MedPersonal_did : null


		},
		callback: function(options, success, response) {
			var responseText = Ext.util.JSON.decode(response.responseText);
			var form = (Ext.getCmp('personBskRegistryDataWindow'))?Ext.getCmp('personBskRegistryDataWindow'):Ext.getCmp('personBskRegistryWindow');
			

			
			if (success === true/*responseText.Error_Code == null && responseText.Error_Message == null*/) { 
				if (Ext.getCmp('personBskRegistryDataWindow')) {
					form.findById('PBRW_tabpanelBSK').setActiveTab(form.findById('PBRW_infotab'));
					form.clickToPN = 0;
					form.MorbusType_id = MorbusType_id;
					var params = {
						action: 'add',
						Person_id:  form.findById('PBRW_infoPacient').getFieldValue('Person_id'),
						MorbusType_id: MorbusType_id
					}
					form.TreePanel.selModel.selNode = null;
					var root = form.TreePanel.getRootNode();
					form.TreePanel.getLoader().baseParams.Person_id = params.Person_id;
					form.TreePanel.getRootNode().loaded = true;
					form.TreePanel.getRootNode().loading = false;
					form.TreePanel.getLoader().load(root,function(){
						form.TreePanel.getRootNode().expand(false);
						form.TreePanel.selModel.select(form.TreePanel.getRootNode().findChild('MorbusType_id',params.MorbusType_id));
						form.formParams.node = form.TreePanel.getSelectionModel().getSelectedNode();
						form.formParams.listMorbusType_id.push(params.MorbusType_id);
						form.loadBskRegistryData(params);
					});
				} else {

					var GridObjectsUser = Ext.getCmp('GridObjectsUser').getGrid();
					var MorbusStore = GridObjectsUser.getStore();
					MorbusStore.reload();
					var SelectionModel = GridObjectsUser.getSelectionModel();

				
					MorbusStore.on(
						'load', function(){
							if(!form.clickToRow){//Событие должно срабатывать по клику на добавить один раз
								//console.log('Выход');
								return;
							}
							form.clickToRow = false;
							var indexMorbus = MorbusStore.findBy(function(rec) { return rec.get('MorbusType_id') == Number(Ext.getCmp('choiceBSKObjectWindow').lastSelectedMorbusType_id); });
							//console.log('selection==>',Ext.getCmp('GridObjectsUser').getGrid().getSelectionModel().getSelections());
							Ext.getCmp('GridObjectsUser').getGrid().getSelectionModel().clearSelections();
							SelectionModel.selectRow(indexMorbus,false);
							form.listIDSfocus = [];
							Ext.getCmp('information').removeAll();
							//console.log('windowclick');
							Ext.getCmp('GridObjectsUser').clickToRow();
							//Ext.getCmp('addBskDataButton').handler();
						}
					);				
				}
			}
		}
	});	  
  },
  manageListMorbusType : function(listMorbusType_id){
	Ext.getCmp('BSKObjectCombo').getStore().on(
		'load',
		function(){
			listMorbusType_id.push(84); //#168376
			listMorbusType_id = listMorbusType_id.filter(function(item, pos) {
				return listMorbusType_id.indexOf(item) == pos;
			});
			var store = this;
			var recs = store.data.items;			
			for(var k in listMorbusType_id){
				for(var j in recs){
					if(typeof recs[j] == 'object'){
						if(Number(listMorbusType_id[k]) == Number(recs[j].get('MorbusType_id'))){
							store.remove(recs[j]);
						}
					}
				}
			}
		Ext.getCmp('choiceBSKObjectWindow').messageMorbusType();	
		}
	);	 
  },
  messageMorbusType : function() {
	if (Ext.getCmp('BSKObjectCombo').getStore().data.items.length == 0) {
		sw.swMsg.show(
		{
			buttons: Ext.Msg.OK,
			width: 600,
			fn: function() 
			{
				Ext.getCmp('choiceBSKObjectWindow').refresh();
				Ext.getCmp('choiceBSKObjectWindow').hide();
			},
			icon: Ext.Msg.WARNING,
			msg: 'У данного пациента есть все предметы наблюдения',
			title: 'Исключение при выборе предмета наблюдения',			
		});				
	}
  },
  refresh : function(){
		sw.codeInfo.lastObjectName = this.objectName;
		sw.codeInfo.lastObjectClass = this.objectClass;
		if (sw.Promed.Actions.loadLastObjectCode)
		{
			sw.Promed.Actions.loadLastObjectCode.setHidden(false);
			sw.Promed.Actions.loadLastObjectCode.setText('Обновить '+this.objectName+' ...');
		}
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		this.close();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];                           

  } ,     
  show: function (params) {
		Ext.getCmp('BSKObjectCombo').getStore().reload();	

		if(typeof params.listMorbusType_id == 'object'){
			Ext.getCmp('choiceBSKObjectWindow').inp = true;
			this.manageListMorbusType(params.listMorbusType_id);	
		}
	    else{
			Ext.getCmp('choiceBSKObjectWindow').inp = false;
			if(typeof params.person != 'undefined'){
				Ext.getCmp('choiceBSKObjectWindow').person = params.person;
			}
		}			
	    sw.Promed.swBSKSelectWindow.superclass.show.apply(this, arguments);
  },
  listeners : {
		'hide': function() {
			if (this.refresh)
				this.onHide();
		},
		'close': function() {
			if (this.refresh)
				this.onHide();
		} 		
  }
});