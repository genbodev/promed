/**
 * swBSKSelectWindow - окно выбора предмета наблюдения регистра БСК
 * 
 */

sw.Promed.swBSKSelectWindow = Ext.extend(sw.Promed.BaseForm, {
    id: 'choiceBSKObjectWindow',
    MorbusType_id: false,
    modal: true,
    title: lang['vyibor_predmeta_nablyudeniya_registra_bsk'],
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
                                url: '/?c=ufa_BSK_Register_User&m=getBSKObjects',
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
                                    text: lang['vyibrat'],
                                    id: 'getMorbusType_id',
                                    handler: function () {


                                        var MorbusType_id = Ext.getCmp('BSKObjectCombo').getValue();
										Ext.getCmp('choiceBSKObjectWindow').lastSelectedMorbusType_id = MorbusType_id;;

                                        if (typeof MorbusType_id == 'string') {
                                            sw.swMsg.show(
                                                    {
                                                        icon: Ext.MessageBox.ERROR,
                                                        title: lang['oshibka'],
                                                        msg: lang['neobhodimo_vyibrat_predmet_nablyudeniya'],
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
												//

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
                                    text: lang['otmena'],
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
	Ext.Ajax.request({
		url: '?c=ufa_Bsk_Register_User&m=saveInPersonRegister',
		params: {
			//Person_id   : Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Person_id
			MedPersonal_iid: getGlobalOptions().medpersonal_id,
			PersonRegister_id: null,
			Mode:null,
			Person_id: Ext.getCmp('ufa_personBskRegistryWindow').personInfo.Person_id,
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
			var form = Ext.getCmp('ufa_personBskRegistryWindow');
			

			
			if (/*success === true*/responseText.Error_Code == null && responseText.Error_Message == null) { 

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
	});	  
  },
  manageListMorbusType : function(listMorbusType_id){
	Ext.getCmp('BSKObjectCombo').getStore().on(
		'load',
		function(){
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