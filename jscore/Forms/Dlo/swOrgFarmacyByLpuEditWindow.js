/**
* swOrgFarmacyByLpuEditWindow - окно редактирования прикрепления аптек к подразделениям МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       
* @version      2012.08.15c
* @comment      
*/
var $CbxFirstVal = 1;
var $NameFldLsCombo = lang['vse_lp'];

var LsTypeStore =  new Ext.data.SimpleStore({
fields:
[
{name: 'LsType_id', type: 'int'},
{name: 'LsType_Name', type: 'string'}
],
data: [[1, lang['vse_lp']], [2,lang['ns_i_pv']]]
});   

var $head = {
    layout: 'column',
            id: 'AllPodr',
    items: [
        {
            layout: 'form',
            style: 'font-size: 14px; font-weight: plain; ',
//             style: 'font-size: 14px; font-weight: plain; border-bottom: 2px solid blue',

            border: true,
            //frame: true,
            bodyBorder: true,
            labelAlign: 'right',
            items: [
                {
                    xtype: 'label',
                    frame: true,
                    style: 'padding: 0px 120px; ',
                    text: lang['podrazdeleniya']
                },
                {
                    xtype: 'label',
                    style: 'padding: 0px 150px;',
                    text: lang['gruppa_lp']
                }
            ]
        },
        {
            height : 30,
            border : false,
            cls: 'tg-label'
        }
    ]

}

sw.Promed.swOrgFarmacyByLpuEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['prikreplenie_aptek_k_mo'] + ': ' + lang['redaktirovanie'],
        titleBase: lang['prikreplenie_aptek_k_mo'] + ': ' + lang['redaktirovanie'],
	id: 'swOrgFarmacyByLpuEditWindow',
        //frame: true,
	border: true,
	width: 760,
	height: 600,
	autoScroll: true,
	//autohight: true,
	//autoHeight: true,
	maximizable: true,        
	layout:'form',
	codeRefresh: true,
	closeAction: 'hide',
	objectName: 'swOrgFarmacyByLpuEditWindow',
	objectSrc: '/jscore/Forms/Dlo/swOrgFarmacyByLpuEditWindow.js',
	onHide: Ext.emptyFn,
   
    getCheckbox : function($id){
        var item = {
                    layout: 'column',
                     //width: 700,        
                    //  Параметры прикрепления подразделения МО к аптеке
                    items: [
                        {layout: 'form', 
                            style: 'padding: 5px 0px; font-size: 12px', 
                            width: 400,
                            //labelWidth: 300, 
                            //labelAlign: 'right',
                            items: [
                                {xtype: 'checkbox', 
                                    //id: 'checkbox_1', 
                                    labelSeparator: '', 
                                    //fieldLabel: 'Подразделение'
                                }
                            ]},
                        {
                            layout: 'form', 
                            //width: 250,
                            labelWidth: 50,
                            width: 250,
                            items: [     
                   
                        new Ext.form.ComboBox({
                                    //id : 'LsTypeCombo',
                                    fieldLabel: '',
                                    labelSeparator: '', 
                                    store: LsTypeStore,
                                     width: 150,
                                     //style: 'padding: 0px 10px;x', 
                                    //id: 'cb_' + $id,
                                    id: 'cb_' + $id,
                                    displayField:'LsType_Name',
                                    valueField: 'LsType_id',
                                    editable : false,
                                    
                                    triggerAction : 'all',
                                    mode: 'local',
                                    //value: 0,
                                    allowBlank: false,
                                    typeAhead: true
 
                                    })
                            ]}
                    ]

                };
                
    return item;
},
    
getChexboxes : function(ids){
    var checkboxes = [];
    var $i = 0;
    var formStoreCnt = Ext.getCmp('FormPanel').formStore.getCount();
    for(var k in ids){
        $i += 1;
        var $id = ids[k].id;
        var item = this.getCheckbox($id);

        var $ch = item.items[0].items[0];
         $ch.id = 'ch_'+ids[k].id; 
         $ch.boxLabel = ids[k].LpuBuilding_Name; 
         $ch.cbx_idx = 'cb_'+ids[k].id;

        var cbx = item.items[1].items[0];
        var $id = 'ch_'+ids[k].id; 
        
        
        cbx.store.data.items[0].data.LsType_Name = $NameFldLsCombo;   
            if (ids[k].moAttach == 1) {
                //Ext.getCmp($id).checked = true
                $ch.checked = true
                 cbx.show();
            } else {
                $ch.checked = false;
                cbx.hide();
                //$rd.disabled = true;
            }
   
            var $CbxValLs = LsTypeStore.data.items[0].data.LsType_id;
            var $CbxValNs = LsTypeStore.data.items[1].data.LsType_id;
            cbx.setValue($CbxValLs);
            var $data = Ext.getCmp('FormPanel').formStore.data.items[k].data;
            

            if ($i <= formStoreCnt) {
                /*
                 * Настройки комбобоксов подразделений
                 * ...LS = Лекартсвенные препараты
                 * ... NS - наркотические средства
                 * moAttach - признак прикрепления
                 * 
                 */
                if (this.params.OrgFarmacy_IsNarko == 'false') {
                    cbx.disabled = true;
                    //alert(1);
                } else if ($data.moAttach == 0 & $data.moAttachLS == 0 & $data.moAttachNS == 0) {
                    //  Нет прикрепления у подразделения
                    cbx.disabled = false;
                    //alert(2);
                } else if ($data.moAttach == 0 & $data.moAttachLS == 0 & $data.moAttachNS == 1) {
                    //  По НС подразделение прикреплено к другой аптеки
                     cbx.disabled = true;
                     //alert(3);
                } else if ($data.moAttach == 0 & $data.moAttachLS == 1 & $data.moAttachNS == 0) {
                    //  По ЛП подразделение прикреплено к другой аптеки
                     cbx.setValue($CbxValNs);
                     cbx.disabled = true;
                     //alert(4);
                 } else if ($data.moAttach == 1 
                            & ($data.OrgFarmacyLS_id == this.params.OrgFarmacy_id || $data.OrgFarmacyLS_id == undefined )
                            & ($data.OrgFarmacyNS_id == this.params.OrgFarmacy_id || $data.OrgFarmacyNS_id == undefined ))
                    {
                        // Есть прикрепление к текущей аптеке
                        //console.log($data.OrgFarmacyNS_id + ' == ' + this.params.OrgFarmacy_id);
                        if ($data.OrgFarmacyNS_id == this.params.OrgFarmacy_id & $data.OrgFarmacyLS_id == undefined) {
                            // Есть прикрепление к текущей аптеке По ЛП
                            cbx.setValue($CbxValNs);
                            //alert(6);
                        }
                         cbx.disabled = false;
                    } else if ($data.moAttach == 1 & $data.OrgFarmacyLS_id != this.params.OrgFarmacy_id) {
                        cbx.setValue($CbxValNs);
                        cbx.disabled = true;
                        //alert(7);
                    } else {
                        cbx.disabled = true;
                        //alert(8);
                    }
            }
                    
                    
            
            $ch.moAttach = ids[k].moAttach;
          //$rd.fieldLabel = 'Выписываются '+ids[k]; 
           
           
           
                                   $ch.listeners = {
                'check':
                function () {
    
                    if (this.checked)
                        Ext.getCmp(this.cbx_idx).show();
                    else
                        Ext.getCmp(this.cbx_idx).hide();
                }
            }
            checkboxes.push(item);    
        }
//    }
    
    return checkboxes.slice(0, checkboxes.length - 2);
},
getDate: function(WhsDocumentCostItemType_id) {
		this.FormPanelGroup.removeAll();

		this.FormPanel.formStore.load({
			params: {
				Lpu_id: this.params.Lpu_id,
				OrgFarmacy_id: this.params.OrgFarmacy_id,
				WhsDocumentCostItemType_id: WhsDocumentCostItemType_id
			},
			callback: function () {

				var form = Ext.getCmp('swOrgFarmacyByLpuEditWindow');
				var rec = [];

				var formStoreCnt = Ext.getCmp('FormPanel').formStore.getCount();
				var allPodrVkl = 1;  // признак возможности привязки  всех подразделений 
				allPodrVkl = 0; //  Принудительно скрываем все подразделения
				if (formStoreCnt > 0) {
					for (var i = 0; i < formStoreCnt; i++) {
						var param = {};
						var data = Ext.getCmp('FormPanel').formStore.data.items[i].data;

						param.id = data.LpuBuilding_id;
						param.LpuBuilding_Name = data.LpuBuilding_Name;
						param.moAttach = data.moAttach;
						if (data.Attach_Other == 1 || param.moAttach == 1 || 1 == 1)
								//  Принудительно скрываем все подразделения
								{
									allPodrVkl = 0;
								}

						rec.push(param);

					}
				}
				form.FormPanelGroup.add($head);
				var ch = form.getChexboxes(rec);

				var allPodr = 0;
				var Podr = 0;


				for (var k in ch) {
					if (typeof ch[k] == 'object') {
						var $moAttach = ch[k].items[0].items[0].moAttach

						form.FormPanelGroup.add(ch[k]);
						if ($moAttach == 2)
							allPodr = 1;
						else if ($moAttach == 1)
							Podr = 1;
					}
					;
				}
				if (allPodrVkl == 0) {
					Ext.getCmp('ch_0').setValue(false);
					Ext.getCmp('FarmacyByLpu_PanelAllPodr').hide();
				} else {
					//Ext.getCmp('ch_0').enable();


					Ext.getCmp('FarmacyByLpu_PanelAllPodr').show()


					if ((formStoreCnt = 0 || Podr == 0) & allPodrVkl == 1) {
						Ext.getCmp('ch_0').setValue(true)

					} else {
						if (allPodr == 1) {
							Ext.getCmp('ch_0').setValue(true);

						} else {
							Ext.getCmp('ch_0').setValue(false);
						}
					};
				};
				form.FormPanelGroup.doLayout();
			}
		});

		Ext.getCmp('swOrgFarmacyByLpuEditWindow').syncShadow();//перерисовка тени под изменившееся окно
},
        doSave: function(){
            
             var form = Ext.getCmp('swOrgFarmacyByLpuEditWindow');
            var formStoreCnt = Ext.getCmp('FormPanel').formStore.getCount();
            if (formStoreCnt > 0) {
                var $Param = [];
               
                for (var i=0; i<formStoreCnt; i++){
                    var $rec = {};
                     
                    var $data = Ext.getCmp('FormPanel').formStore.data.items[i].data;
                    var $ch = Ext.getCmp('ch_' + $data.LpuBuilding_id);
                    var $cb = Ext.getCmp('cb_' + $data.LpuBuilding_id);
                    var $val = $cb.getValue();  //!= undefined

                    $rec.LpuBuilding_id = $data.LpuBuilding_id;
                    $rec.Lpu_id = this.params.Lpu_id;
                    $rec.OrgFarmacy_id = this.params.OrgFarmacy_id;
;
                    if ($data.moAttach == 0 & !$ch.checked)
                        $rec.action = 'none'
                    else if ($data.moAttach == 1 & $ch.checked) {
                        
                        $rec.typeLs = $val;
                        var $moAttachNS = $data.moAttachNS;

                        if ($val == 1 &  $data.moAttachLS == 1)
                            $rec.action = 'none'
                        else if ($val == 1 &  $data.moAttachLS == 0) {
                            $rec.action = 'update';
                            $rec.OrgFarmacyIndex_id = $data.OrgFarmacyIndexLS_id;
                        } 
                        else if ($val == 2 &  $moAttachNS == 1) {
                            
                            $rec.action = 'none'
                             
                    } else 
                        if ($val == 2 &  $data.$data.moAttachNS == 0) {
                            $rec.action = 'update';
                            $rec.OrgFarmacyIndex_id = $data.OrgFarmacyIndexNS_id;
                           
                        }
                            
                    } else if ($data.moAttach == 0 & $ch.checked) {
                        
                        $rec.action = 'insert'
                        $rec.typeLs = $val;
                        //alert(11);
                         //alert($rec.OrgFarmacyIndex_id);
                    } else if ($data.moAttach == 1 & !$ch.checked) {
                        $rec.action = 'delete';  
                        //alert('$data.moAttachLS = ' + $data.moAttachLS + ': $data.OrgFarmacyIndexNS_id = ' + $data.OrgFarmacyIndexNS_id);
                           
                        if ($data.moAttachLS == 1 & $data.OrgFarmacyLS_id == this.params.OrgFarmacy_id) {
                            $rec.OrgFarmacyIndex_id = $data.OrgFarmacyIndexLS_id;
                            //alert(1);
                        } else if ($data.moAttachNS == 1 & $data.OrgFarmacyNS_id == this.params.OrgFarmacy_id) {
                            $rec.OrgFarmacyIndex_id = $data.OrgFarmacyIndexNS_id;
                             //alert(2);
                        }   
                    } 
                    //alert($rec.OrgFarmacyIndex_id);
                    
                    if ($rec.action != 'none')
                        $Param.push($rec);                   
                }

               var $arr = Ext.util.JSON.encode($Param);

               Ext.Ajax.request({
                    url: '/?c=Drug&m=saveMoByFarmacy',
                    method: 'POST',
                    params: {'arr' : $arr,
							 'WhsDocumentCostItemType_id' : Ext.getCmp('FarmacyByLpu_WhsDocumentCostItemType').value},
                    success: function(response, opts) {
                        //alert('success 0')
                        //alert(form.params.parent_id);
                        if (form.params.parent_id == 'OrgFarmacyByLpuViewWindow') {
                                            Ext.getCmp(form.params.parent_id).fireEvent('success', 'swOrgFarmacyByLpuEditWindow', 'refresh');
                         //alert('success 1')   
                            }
                        }
               })
            }
                                    
            
        },
                     
  initComponent: function() {
		var vEdiit = false;   
		var form = this;
		

		this.FormPanel =
			new Ext.form.FormPanel({
				frame: true,
				id: 'FormPanel',
                                labelAlign: 'right',
				formParams: null,
				items: [
                                    {
                                        fieldLabel: lang['mo'],
                                        frame: true,
                                        id: 'FarmByLpu_LpuName',
                                        readOnly: true,
                                        width: 400,
                                        height: 20,
                                        xtype: 'textarea'
                                    },
                            {
                                layout: 'form',
                                style: 'padding: 0px 105px;',
                                id: 'FarmacyByLpu_PanelAllPodr',
                                labelWidth: 120,
                                items: [
                              {
                                                    xtype : 'checkbox', 
                                                    id: 'ch_0', 
                                                    labelSeparator: '', 
                                                    
                                                    fieldLabel: lang['vse_podrazdeleniya'],
                                                    checked: 'true',
                                                    listeners: {
                                                        'check': function ( newValue, oldValue ) {
                                                           
                                                            if (Ext.getCmp('ch_0').checked) {
                                                                Ext.getCmp('FormPanelGroup').hide();
                                                                Ext.getCmp('LsTypeComboPwanel').show(); 
                                                            } else {
                                                                 Ext.getCmp('FormPanelGroup').show();
                                                                 Ext.getCmp('LsTypeComboPwanel').hide(); 
                                                                 
                                                            }
                                                             Ext.getCmp('swOrgFarmacyByLpuEditWindow').syncShadow();//перерисовка тени под изменившееся окно
                                                         }
                                                    }
                                                }
                                ]},              
                                               
                             {
//                                disabled: vEdiit,
                                fieldLabel: lang['apteka'],
                                
                                id: 'FarmByLpu_FarmName',
                                readOnly: true,
                                width: 400,
                                //autoHeight: false,
                                height: 20,
                                xtype: 'textarea'
                            },
							{
								layout: 'form',
								hidden: getRegionNick() != 'ufa',
								items: [
								{
									xtype: 'swwhsdocumentcostitemtypecombo',
									fieldLabel: 'Программа ЛЛО',
									name: 'WhsDocumentCostItemType_id',
									id: 'FarmacyByLpu_WhsDocumentCostItemType',
									autoLoad: false,
									width: 400,

									 listeners: {
										change: function(combo, newValue, oldValue) {
											form.getDate(newValue);
										}
									 }

								}
								]
						},
                            {
                                    layout: 'column', 
                                            id: 'AllPodr',
                                             hidden: 'true',
                                    items: [ 
                                        {
                                            layout: 'form', 
                                            style: 'padding: 0px 20px;', 
                                            labelWidth: 300, 
                                            labelAlign: 'right'
                                }
                            ]},
                        {
                        layout: 'form', 
                        style: 'padding: 0px 100px;', 
                        id: 'LsTypeComboPwanel',
                        width: 550, labelWidth: 150,
                        items: [
                            new Ext.form.ComboBox({
                                    id : 'LsTypeCombo',
                                    fieldLabel: lang['gruppa_medikamentov'],
                                    labelSeparator: '',
                                    //autoLoadData:false,
                                    store: LsTypeStore,
                                    displayField:'LsType_Name',
                                    valueField: 'LsType_id',
                                    editable : false,
                                    width: 150,
                                    triggerAction : 'all',
                                    mode: 'local',
                                    allowBlank: false,
                                    typeAhead: true
                                })
                            ]}
                    ]

			});
                        
                        /*
                        * хранилище для доп сведений
                        */
                        this.FormPanel.formStore = new Ext.data.JsonStore({
			fields: [
                                'LpuBuilding_id',
				'LpuBuilding_Name',
                                'moAttach',
                                'moAttachLS',
                                'OrgFarmacyLS_id',
                                'OrgFarmacyIndexLS_id',
                                'moAttachNS',
                                'OrgFarmacyNS_id',
                                'OrgFarmacyIndexNS_id',
                                'Attach_Other'
			],

			url: '/?c=Drug&m=GetMoByFarmacy',
			key: 'LpuBuilding_id',
			root: 'data'
                    });
                
                         this.FormPanelGroup =
			new Ext.form.FormPanel({
                            frame: true,
                            //bodyBorder: false,
                            id: 'FormPanelGroup',
                             autoHeight: true,
                             hidden: 'false',
                            style: 'border: 0px',
                             bodyStyle: 'padding: 0px'
                        });
		 
		Ext.apply(this, {
			items: [
			this.FormPanel,
                        this.FormPanelGroup
			],
					
			buttons: [
			{
                            text: BTN_FRMSAVE,
                            tabIndex: TABINDEX_PEF + 60,
                            iconCls: 'save16',
                            tabIndex: TABINDEX_PRESVACEDITFRM + 31,
                            id: 'FarmByLpu_SaveButton',
                             handler: function () {
                                 this.doSave();
                                 this.hide();
                             }.createDelegate(this)
                                  
			},{
				text: '-'
			},
                        {      text: BTN_FRMHELP,
                                iconCls: 'help16',
                                tabIndex: TABINDEX_PRESVACEDITFRM + 32,
                                handler: function(button, event)
                                {
                                        ShowHelp(this.ownerCt.titleBase);
                                }
			},
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'EPLSIF_CancelButton',
				onTabAction: function () {
					Ext.getCmp('FarmByLpu_VaccineCombo').focus();
				}.createDelegate(this),
				
				tabIndex: TABINDEX_PRESVACEDITFRM + 33,
				text: '<u>З</u>акрыть'
			}
			]
				  
		});
				
		sw.Promed.swOrgFarmacyByLpuEditWindow.superclass.initComponent.apply(this, arguments);
	},
	 
	show: function(record) {

		sw.Promed.swOrgFarmacyByLpuEditWindow.superclass.show.apply(this, arguments);  
                var form = this;
				this.params = record;
				//console.log ('record', record);
                
                Ext.getCmp('FarmByLpu_FarmName').setValue(record.OrgFarmacy_Name);
                Ext.getCmp('FarmByLpu_LpuName').setValue(record.Lpu_Name);
						
				Ext.getCmp('FarmacyByLpu_WhsDocumentCostItemType').getStore().load({
					callback: function(){
						Ext.getCmp('FarmacyByLpu_WhsDocumentCostItemType').setValue(null);
						if (record.WhsDocumentCostItemType_id) {
							Ext.getCmp('FarmacyByLpu_WhsDocumentCostItemType').setValue(record.WhsDocumentCostItemType_id);
						}
					}
				});
				
				
                if (record.OrgFarmacy_IsNarko == 'false')
                    $NameFldLsCombo = lang['vse_lp_krome_ns_i_pv']
                else $NameFldLsCombo = lang['vse_lp']
                
                var $CbxValLs = LsTypeStore.data.items[0].data.LsType_id;
                Ext.getCmp('LsTypeCombo').store.data.items[0].data.LsType_Name = $NameFldLsCombo; 
                Ext.getCmp('LsTypeCombo').setValue($CbxValLs);
				
				this.getDate(Ext.getCmp('FarmacyByLpu_WhsDocumentCostItemType').value);
				
	}

});

