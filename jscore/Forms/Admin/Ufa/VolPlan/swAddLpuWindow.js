sw.Promed.swAddLpuWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	width : 500,
	height : 140,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['vyibor_mo'],
        Request_id: null,
        Lpu_id: null,
        arr:[],
	/**
	 * Входящие параметры - список Lpu_id для отображения в списке выбора
	 * @type {Array}  Ext.getCmp('idFsfrm').reset();
	 */
	params: null,
        listeners:{
		hide:function () {
                    this.onHide();
                    var formPanel = Ext.getCmp('SelectLpuForm');
                    formPanel.getForm().reset();
                    this.hide();
                    this.close();
                    window[this.objectName] = null;
                    delete sw.Promed[this.objectName];
                    
                        //this.destroy();
		}
	},	
	/**
	 * Отображение окна
	 */
	show: function() {
                
		sw.Promed.swAddLpuWindow.superclass.show.apply(this, arguments);
                var form = this;
                var formPanel = Ext.getCmp('SelectLpuForm');
                    formPanel.getForm().reset();
                form.Request_id = arguments[0].VolRequest_id;
                
                Ext.Ajax.request({ 
                    url: '/?c=VolPeriods&m=getLpuList', 
                    params: { 
                        Request_id: form.Request_id
                    },

                    success: function(result){
                        var resp_obj = Ext.util.JSON.decode(result.responseText); 

                        var ind = 0;
                        var indEnd = resp_obj.length;
                        //var arr=[];
                        while (ind<indEnd) {                    
                            form.arr.push(resp_obj[ind].Lpu_id)

                            ind++;
                        };
                        Ext.getCmp('SLW_Lpu_id').getStore().filterBy(function(rec) {
				return (rec.get('Lpu_id').toString().inlist(form.arr));
			});
                        Ext.getCmp('SLW_Lpu_id').setBaseFilter(function(rec) {
                                                                            return (rec.get('Lpu_id').toString().inlist(form.arr));
                                                                    });
                        console.log(form.arr);
                    }
                });
                

		if ( arguments[0].params ) {
			this.params = arguments[0].params;
		}
                
		
		var LpuField = form.findById('SLW_Lpu_id');
		//loadComboOnce(form.findById('SLW_Lpu_id'), 'Lpu', function() {
		LpuField.getStore().clearFilter();
		// Выбираем первое МО в списке
//		if (getGlobalOptions().TOUZLpuArr && getGlobalOptions().TOUZLpuArr.length > 0 && !isSuperAdmin() && !getGlobalOptions().isMinZdrav) {
//			this.params = getGlobalOptions().TOUZLpuArr;
//		} else if ( !getGlobalOptions().superadmin && !isUserGroup(['medpersview', 'ouzuser', 'OuzSpecMPC', 'ouzadmin', 'ouzspec', 'ouzchief', 'roszdrnadzorview']) && !(getGlobalOptions().isMinZdrav && getGlobalOptions().orgtype == 'touz' && isUserGroup(['ouzspec'])) ) {
//			// Фильтруем МО, чтобы отображались только те, идентификаторы которых пришли как параметр
			//this.params = (this.params)?this.params:getGlobalOptions().lpu;
		/*} /*
		это что-то неправильное, на момент открытия формы справочник уже загружен, зачем его загружать повторно
		else {
			if(getGlobalOptions().region.nick!='ufa')
				LpuField.getStore().load();
			
        }*/

		var i, lpu_id;

		LpuField.getStore().filterBy(function(record, id) {
			if ( record.get('Lpu_IsAccess') == 1 ) {
				return false;
			}

			var ret = true;

			if ( this.params ) {
				ret = false;

				for (i = 0; i < this.params.length; i++) {
					if ( this.params[i] == record.get('Lpu_id') ) {
						if ( Ext.isEmpty(lpu_id) ) {
							lpu_id = record.get('Lpu_id');
						}
						ret = true;
						break;
					}
				}
			}

			return ret;
		}.createDelegate(this));

		// Для непустого this.params lpu_id получили в процессе фильтрации
		if ( !this.params ) {
			// Если входных параметров нет (не пришел в форму список МО), то выбираем текущее МО
			var index = LpuField.getStore().findBy(function(rec) {
				return (rec.get('Lpu_id') == getGlobalOptions().lpu_id);
			});

			if ( index >= 0 ) {
				lpu_id = getGlobalOptions().lpu_id;
			}
		}

		if ( !Ext.isEmpty(lpu_id) ) {
			LpuField.setValue(lpu_id);
		}

		LpuField.focus(true, 100);

		var record = LpuField.getStore().getById(LpuField.getValue());
		LpuField.fireEvent("select", LpuField, record);
		this.buttons[0].enable();
		//}.createDelegate(this));
	}, //end show()


	/**
	 * Запрос к серверу после выбора МО
	 */
	submit: function() {
		var form = this.findById('SelectLpuForm').getForm();
		
		this.buttons[0].disable();
		
		if (!form.isValid()) {
			Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'],
					lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			this.buttons[0].enable();
			return;
		}
		form.submit({
			success : function(form, action) {
				this.hide();
				setCurrentLpu(action.result);
				
				// Открытие АРМа по умолчанию
				// TODO: Надо ли оно здесь
				// sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
				
				this.buttons[0].enable();
			}.createDelegate(this),
			failure : function(form, action) {
				
				if  ((action.result) && (action.result.Error_Code))
					Ext.Msg.alert("Ошибка", '<b>Ошибка '
									+ action.result.Error_Code
									+ ' :</b><br/> '
									+ action.result.Error_Msg);
				this.buttons[0].enable();
			}.createDelegate(this)
		});
	}, //end submit()
        
        doSave:  function() {
            var wnd = this;
            wnd.Lpu_id = Ext.getCmp('SLW_Lpu_id').getValue();
            var RequestEditWindow = getWnd('swVolRequestEditWindow');
            var request_id = RequestEditWindow.VolRequest_id;
            var SprVidMp_id = RequestEditWindow.SprVidMp_id;

            var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет создание заявки..."});
            loadMask.show();
                //console.log('123',RequestEditWindow);
//		if ( !this.form.isValid() ) {
//			sw.swMsg.show( {
//				buttons: Ext.Msg.OK,
//				fn: function() {
//					wnd.findById('drpeVolPeriodEditForm').getFirstInvalidEl().focus(true);
//				},
//				icon: Ext.Msg.WARNING,
//				msg: ERR_INVFIELDS_MSG,
//				title: ERR_INVFIELDS_TIT
//			});
//			return false;
//		}
                //savePeriod: function() {
                                //var data = this.getData();
                                //console.log(data);
                                
            Ext.Ajax.request({
                url: '/?c=VolPeriods&m=addLpu2Request',
                params: 
                {
                    Request_id: request_id,
                    Lpu_id: wnd.Lpu_id,
                    SprRequestStatus_id: 1,
                    SprVidMp_id: SprVidMp_id
                },
                success: function (response) 
                {
                    loadMask.hide();
                    Ext.getCmp('idVolRequestsGrid2').getGrid().getStore().reload();
                },
                failure:function () {
                    sw.swMsg.alert(lang['oshibka'], 'Что-то пошло не так');
                },
            });  
            //};

            //this.submit();
            //var mainWnd = getWnd('swVolPeriodViewWindow');
            RequestEditWindow.reloadGrid();
            wnd.hide();
            return true;		
	},

	/**
	 * Конструктор
	 */
	initComponent: function() {
		var form = this;


		var TextTplMark =[
			'<div style="font-size: 11px;">{text}</div>'
		];
		this.TextTpl = new Ext.Template(TextTplMark);

		this.TextPanel = new Ext.Panel({
			html: '&nbsp;',
			style: 'margin-left:55px',
			id: 'slwTextPanel',
			autoHeight: true
		});
                


    	Ext.apply(this, {
			items : [new Ext.form.FormPanel({
				id : 'SelectLpuForm',
				height : 75,
				layout : 'form',
				border : false,
				frame : true,
				labelWidth : 50,
				items : [{
                                            xtype: 'fieldset',
                                            style : 'padding: 10px;',
                                            autoHeight: true,
                                            id: 'idFsfrm',
                                            items : [{
                                                    anchor : "95%",
                                                    editable : true,
                                                    //editable: !(getGlobalOptions().TOUZLpuArr && getGlobalOptions().TOUZLpuArr.length > 0 && !isSuperAdmin() && getGlobalOptions().isMinZdrav),
                                                    ctxSerach: true,
                                                    forceSelection: true,
                                                    hiddenName : 'Lpu_id',
                                                    fieldLabel: lang['mo'],
                                                    id : 'SLW_Lpu_id',
                                                    lastQuery : '',
                                                    listeners: {
                                                            'blur': function(combo) {
                                                                    if ( combo.getStore().findBy(
                                                                        function(rec) 
                                                                        { 
                                                                            return (rec.get(combo.displayField) == combo.getRawValue()) 
                                                                        }
                                                                                ) < 0  ) 
                                                                    {
                                                                        combo.clearValue();
                                                                        var p = {text:'&nbsp;'};
                                                                        form.TextTpl.overwrite(form.TextPanel.body, p);
                                                                        form.TextPanel.render();
                                                                    }
                                                            },
                                                            'select': function(combo, record, index) {
                                                                    var p = {text:'&nbsp;'};
                                                                    if(record) {
                                                                            if ( record.get('Lpu_EndDate') && record.get('Lpu_EndDate') != '' ) {
                                                                                    p.text = '<span style="color: red;">МО закрыто '+record.get('Lpu_EndDate')+'</span>';
                                                                            } else {
                                                                                    p.text = record.get('Lpu_Name');
                                                                            }
                                                                            /*if (isSuperAdmin()) {
                                                                                    p.text = p.text + ' [ id: '+record.get('Lpu_id')+' ]';
                                                                            }*/
                                                                    }
                                                                    form.TextTpl.overwrite(form.TextPanel.body, p);
                                                                    form.TextPanel.render();

                                                            },
                                                            'keydown': function (inp, e) {
                                                                    inp.getStore().filterBy(function(rec) {
                                                                            return (rec.get('Lpu_id').toString().inlist(form.arr));
                                                                    });
                                                                    if (e.shiftKey == false && e.getKey() == Ext.EventObject.ENTER)
                                                                    {
                                                                            inp.fireEvent("blur", inp);
                                                                            e.stopEvent();
                                                                            this.submit();
                                                                    }
                                                            }.createDelegate(this),
                                                            'keypress': function (inp, e) {
                                                                    inp.getStore().filterBy(function(rec) {
                                                                            return (rec.get('Lpu_id').toString().inlist(form.arr));
                                                                    });
                                                                    
                                                            }.createDelegate(this),
                                                            'keyup': function (inp, e) {
                                                                    inp.getStore().filterBy(function(rec) {
                                                                            return (rec.get('Lpu_id').toString().inlist(form.arr));
                                                                    });
                                                                    
                                                            }.createDelegate(this)
                                                    },
                                                    listWidth : 500,
                                                    tpl: new Ext.XTemplate(
                                                            '<tpl for="."><div class="x-combo-list-item">',
                                                            '{[(values.Lpu_EndDate && values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыто "+ values.Lpu_EndDate /* Ext.util.Format.date(Date.parseDate(values.Lpu_EndDate.slice(0,10), "Y-m-d"), "d.m.Y")"*/ + ")" : values.Lpu_Nick ]}&nbsp;',
                                                            '</div></tpl>'
                                                    ),
                                                    width : 420,
                                                    xtype : 'swlpulocalcombo'
                                            }, this.TextPanel
                                    ]
			}],
			url : C_USER_SETCURLPU
			})],
			buttons : [{
				text : lang['vyibrat'],
				iconCls : 'ok16',
				handler : function(button, event) {
					//this.submit();
                                        this.doSave();
				}.createDelegate(this)
			},
			HelpButton(this)],
			buttonAlign : "right"
		});
		sw.Promed.swAddLpuWindow.superclass.initComponent.apply(this, arguments);
	} //end initComponent()
});