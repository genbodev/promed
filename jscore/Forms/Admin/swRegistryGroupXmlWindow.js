/**
* swRegistryGroupXmlWindow - окно групповой выгрузки реестра в XML.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @version      29.09.2013
*
*
* @input array: Registry_id - ID регистров
*/

sw.Promed.swRegistryGroupXmlWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
    resizable : true,
	closeAction: 'hide',
	draggable: true,
	id: 'RegistryGroupXmlWindow',
	title: lang['formirovanie_xml'],
	width: 600,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() 
	{
		this.TextPanel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'RegistryGroupXmlTextPanel',
			html: lang['vyigruzka_dannyih_reestrov_v_formate_xml']
		});
		
		this.Panel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'RegistryGroupXmlPanel',
			labelAlign: 'right',
			labelWidth: 100,
			items: [this.TextPanel]
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				id: 'rgxfOk',
				handler: function() 
				{   
					this.ownerCt.createXML();
				},
				iconCls: 'refresh16',
				text: lang['sformirovat']
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'rgxfOk',
				text: BTN_FRMCANCEL
			}],
			items: [this.Panel]
		});
		sw.Promed.swRegistryGroupXmlWindow.superclass.initComponent.apply(this, arguments);
	},
	
	listeners: 
	{
		'hide': function() 
		{
			if (this.refresh)
				this.onHide();
		}
	},
    
	createXML: function(addParams) 
	{  
		var Registry_id = this.Registry_id;
	    var Registry_Num = this.Registry_Num;
    	var RegistryType_id = this.RegistryType_id;
		var KatNasel_id = this.KatNasel_id;
		var form = this;
        
		form.getLoadMask().show();

		var params = {
				Registry_id     : Ext.util.JSON.encode(Registry_id),
                Registry_Num    : Ext.util.JSON.encode(Registry_Num),				
                RegistryType_id : Ext.util.JSON.encode(RegistryType_id),
				KatNasel_id     : Ext.util.JSON.encode(KatNasel_id)
	 	};
/***********************
*/			
        console.log('AFTER PARAMS'); 
        //console.log(this);
        console.log(params); 
/***********************
*/            
		if (addParams != undefined) {
			for(var par in addParams) {
				params[par] = addParams[par];
			}
		} else {
			addParams = [];
		}

  
        


		Ext.Ajax.request(
		{
			url: form.formUrl,
			params: params,
			callback: function(options, success, response) 
			{    
				form.getLoadMask().hide();
				if (success)
				{   
				    var result = Ext.util.JSON.decode(response.responseText);
				    console.log(Object.keys(result));
                    console.log(result);
                    
                    var resString = '';
                    
                    for(var key in result) {
                        var obj = result[key];
                        
                        console.log('RESULT XML');
                        console.log(obj);
                         
                        if(key != 'success' && key != 'big'){

                            if(obj.Error_Code){
                                console.log('no: ' + obj.number);
                                resString = resString + "<div>Реестр, счёт №: <b> " + obj.number + "</b> <span style='color:red'>не упакован</span>: " + obj.Error_Msg + "</div>";
                            }
                            else{
                                console.log('yeap: ' + obj.number);
                                resString = resString + "<div style='margin-bottom: 1em;'>Реестр, счёт №: <b> " + obj.number +  "</b> <span style='color:green'>успешно упакован</span>: <a target='_blank' href='" + obj.Link + "'>Архив ZIP</a></div>";

                            }
                            
                            
                        }  
                        else if(key == 'big'){
                                console.log('big: ' + obj.file_name);
                                resString = resString + "<div style='margin-top: 1em;'>Cкачать одним: <b> <a target='_blank' href='" + obj.file_name + "'>Zip архивом</a></div>";
                        }
 
                    }
                    
                    resString = resString + '<br/>';
                    
                    form.TextPanel.getEl().dom.innerHTML = resString;
					form.TextPanel.render();
                    Ext.getCmp('rgxfOk').disable();
                    
                    
                    
                    /*
					if (!response.responseText) {
						var newParams = addParams;
						newParams.OverrideExportOneMoreOrUseExist[0] = 1;
						newParams.onlyLink[0] = 1;
                           
						form.createXML(newParams);
                        
						return false;
					}
					var result = Ext.util.JSON.decode(response.responseText);
					
                    //console.log(result);
                    return;
                    */
                    
                    /*
					if (result.Error_Code && result.Error_Code == '10') { // Статус реестра "Проведен контроль ФЛК"
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' )
								{
									var newParams = addParams;
									newParams.OverrideControlFlkStatus[0] = 1;
									form.createXML(newParams);
								}
							},
							msg: lang['status_reestra_proveden_kontrol_flk_vyi_uverenyi_chto_hotite_povtorono_otpravit_ego_v_tfoms'],
							title: lang['podtverjdenie']
						});
						
						return false;
					}
					
					if (result.Error_Code && result.Error_Code == '11') { // Уже есть выгруженный XML
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' )
								{
									var newParams = addParams;
									newParams.OverrideExportOneMoreOrUseExist[0] = 2;
									form.createXML(newParams);
								} else {
									var newParams = addParams;
									newParams.OverrideExportOneMoreOrUseExist = 1;
									form.createXML(newParams);									
								}
							},
							msg: lang['fayl_reestra_suschestvuet_na_servere_esli_vyi_hotite_sformirovat_novyiy_fayl_vyiberete_da_esli_hotite_skachat_fayl_s_servera_najmite_net'],
							title: lang['podtverjdenie']
						});
						
						return false;
					}
						
					var alt = '';
					var msg = '';
					form.refresh = true;
					if (result.usePrevXml)
					{
						alt = lang['izmeneniy_s_reestrom_ne_byilo_proizvedeno_ispolzuetsya_sohranennyiy_xml_predyiduschey_vyigruzki'];
						msg = lang['xml_predyiduschey_vyigruzki'];
					}
					if (result.Link) {
						form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить реестр</a>'+msg;
						Ext.getCmp('rgxfOk').disable();
					}
					if (result.success === false) {
						form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
						Ext.getCmp('rgxfOk').disable();
					}
					form.TextPanel.render();
                    */
				}
				else 
				{    /*
					var result = Ext.util.JSON.decode(response.responseText);
                    //console.log(result);
					form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
					form.TextPanel.render();
				    */
                }
			}
		});
	},
  
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
		}
		return this.loadMask;
	},
	show: function() 
	{
		sw.Promed.swRegistryGroupXmlWindow.superclass.show.apply(this, arguments);
		var form = this;
		
       
		form.Registry_id = null;
        form.Registry_Num = null;
		form.RegistryType_id = null;
		form.KatNasel_id = null;
		form.onHide = Ext.emptyFn;
		Ext.getCmp('rgxfOk').enable();
		form.refresh = false;
		form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_gruppyi_reestrov_v_formate_xml'];
		form.TextPanel.render();

		if (!arguments[0] || !arguments[0].Registry_id) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi'] + form.id + lang['ne_ukazanyi_neobhodimyie_vhodnyie_parametryi'],
				title: lang['oshibka']
			});
			this.hide();
		}

		if (arguments[0].Registry_id) 
		{
			form.Registry_id = arguments[0].Registry_id;
		}
		if (arguments[0].Registry_Num) 
		{
			form.Registry_Num = arguments[0].Registry_Num;
		}        
		if (arguments[0].RegistryType_id) 
		{
			form.RegistryType_id = arguments[0].RegistryType_id;
		}
		if (arguments[0].KatNasel_id) 
		{
			form.KatNasel_id = arguments[0].KatNasel_id;
		}
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].url) 
		{
			form.formUrl = arguments[0].url;
		}
		else 
		{
			form.formUrl = '/?c=RegistryUfa&m=exportRegistryGroupToXml';
		}

		this.syncSize();
		this.syncShadow();
	}
});