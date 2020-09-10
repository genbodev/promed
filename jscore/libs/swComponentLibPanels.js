/**
* swComponentLibToolbar - класс тулбара, прописываются ownerCt для кнопок.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
*               Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      04.03.2009
*/

sw.Promed.FormPanel = Ext.extend(Ext.FormPanel,
{
	border:false,
	autoWidth: true,
	autoHeight: true,
	labelAlign: 'right',
	labelWidth: 110,
	bodyStyle:'width:100%;background:#DFE8F6;padding:1px;padding-top:4px;',
	maskOnlyContainer: false,
	initComponent: function()
	{
		sw.Promed.FormPanel.superclass.initComponent.apply(this, arguments);
	},
	loadForm: function(params) {
		var panel = this,
			win = panel.getOwnerWindow(),
			form = panel.getForm(),
			mask = new Ext.LoadMask(panel.getEl(), langs('Загрузка...'));

		if(panel.maskOnlyContainer) {
			mask.show();
		} else if (win.showLoadMask) {
			win.showLoadMask('Загрузка..');
		}

		Ext.Ajax.request({
			params: params,
			url: panel.url,
			callback: function(options, success, response) {
				if(panel.maskOnlyContainer) {
					mask.hide();
				} else if(win.hideLoadMask) {
					win.hideLoadMask();
				}
				var resp_obj = jsonDecode(response.responseText);

				if(!resp_obj || !success || resp_obj.Error_Code || resp_obj.Error_Msg) {
					return;
				}

				if(typeof(panel.beforeSetRequestParams) == "function") {
					panel.beforeSetRequestParams(resp_obj[0]);
				}

				form.setValues(resp_obj[0]);

				if(typeof(panel.afterLoad) === 'function') {
					panel.afterLoad(resp_obj[0]);
				}

			}
		});
	},
	saveForm: function(params) {
		var panel = this,
			win = panel.getOwnerWindow(),
			onSave = Ext.emptyFn,
			form = panel.getForm(),
			mask = new Ext.LoadMask(panel.getEl(), langs('Сохранение..'));

		params = params ? params : {};



		if(params.onSave) {
			onSave = params.onSave;
			delete params.onSave;
		}

		var saveParams = {
			params: form.getValues(),
			url: form.saveUrl,
			callback: function(options, success, response) {
				if(panel.maskOnlyContainer) {
					mask.hide();
				} else if(win.hideLoadMask) {
					win.hideLoadMask();
				}

				var resp_obj = jsonDecode(response.responseText);
				if(!resp_obj || !success || resp_obj.Error_Code || resp_obj.Error_Msg) {
					return;
				}

				onSave(resp_obj);
				if(panel.afterSave) {
					panel.afterSave(resp_obj);
				}
			}
		};

		Ext.applyIf(params.params, saveParams.params);
		Ext.applyIf(params, saveParams);

		if(panel.beforeSave) {
			panel.beforeSave(params.params);
		}

		//функция вызываемая при ошибке валидации
		if( typeof(panel.onValidationError) !== "function" ) {
			panel.onValidationError = function () {
				var inviledFields = panel.getInvalid();
				//todo подумать над раскрытием листа у комбика, переключением вкладок у таббара
				if (inviledFields.length) {
					inviledFields[0].focus();
				}
			};
		}

		if(!form.isValid()) {
			sw.swMsg.alert( langs("Ошибка"), panel.getInvalidFieldsMessage(), panel.onValidationError);
			return
		}

		if(!params.url) {
			sw.swMsg.alert( langs("Ошибка"), langs("У формы не задан url для сохранения"));
			return;
		}

		if(typeof(panel.validateForm) === "function" && !panel.validateForm(params)) {
			return;
		}

		if(panel.maskOnlyContainer) {
			mask.show();
		} else if(win.showLoadMask) {
			win.showLoadMask('Сохранение..');
		}

		if( panel.saveConfirmMsg && typeof panel.saveConfirmCondition == 'function' && panel.saveConfirmCondition() ) {
			sw.swMsg.confirm(
				langs('Сообщение'),
				panel.saveConfirmMsg,
				function(btn) {
					if ( btn == 'yes' ) {
						Ext.Ajax.request(params);
					} else {
						if(panel.maskOnlyContainer) {
							mask.hide();
						} else if(win.hideLoadMask) {
							win.hideLoadMask();
						}
					}
				}
			);

		} else {
			Ext.Ajax.request(params);
		}

	}
});

sw.Promed.Panel = Ext.extend(Ext.Panel,
{
	animCollapse: false,
	initComponent: function()
	{
		sw.Promed.Panel.superclass.initComponent.apply(this, arguments);

		this.addListener({
			'render': function(panel) {
				if (panel.header)
				{
					panel.header.on({
						'click': {
							fn: this.toggleCollapse,
							scope: panel
						},
						'mouseover': {
							fn: function() {
								this.applyStyles('cursor: pointer');
							},
							scope: panel.header
						},
						'mouseout': {
							fn: function() {
								this.applyStyles('cursor: default');
							},
							scope: panel.header
						}
					});
				}
			}
		});
	}
});
Ext.reg('swpromedstandartpanel', sw.Promed.Panel);
/* 2009-07-09 */
sw.Promed.PersonInformationPanel = Ext.extend(Ext.Panel,
{
	additionalFields: [],
	border: false,
	readOnly: false,
	button1Callback: Ext.emptyFn,
	button2Callback: Ext.emptyFn,
	button3Callback: Ext.emptyFn,
	button4Callback: Ext.emptyFn,
	button5Callback: Ext.emptyFn,
	button1OnHide: Ext.emptyFn,
	button2OnHide: Ext.emptyFn,
	button3OnHide: Ext.emptyFn,
	button4OnHide: Ext.emptyFn,
	button5OnHide: Ext.emptyFn,
	collectAdditionalParams: Ext.emptyFn,
	getFieldValue: function(field) {
		var result = '';
		if (this.items.items[0].getStore().getAt(0))
			result = this.items.items[0].getStore().getAt(0).get(field);
		return result;
	},
	height: 130,
	layout: 'border',
	load: function(params) {
		var callback_param = Ext.emptyFn;

		if ( params.callback ) {
			callback_param = params.callback;
		}

		this.personId = params.Person_id;
		this.serverId = params.Server_id;

		this.items.items[0].getStore().removeAll();
		this.items.items[0].getStore().load({
			params: params,
			callback: callback_param
		});

		this.setReadOnly(false);
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible()) //https://redmine.swan.perm.ru/issues/92555 Эта панель дохрена где используется, а для АРМа МЗ ее надо скрыть, поэтому чем перелопачивать туеву хучу форм, сделал такой вот финт ушами
			this.disable();
		else
			this.enable();
		// ищем родительское окно, если есть то устанавливаем возможность редактировать пользователя в зависимости от action/readOnly в родительском окне.
		var ownerCur = this.ownerCt;
		if( typeof ownerCur != "undefined" ) {
			while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
				ownerCur = ownerCur.ownerCt;
			}
			if (typeof ownerCur.checkRole == 'function') {
				if (ownerCur.readOnly || (ownerCur.action && ownerCur.action == 'view')) {
					this.setReadOnly(true);
				}
			}
		}
	},
	panelButtonClick: function(winType) {
		var params = this.collectAdditionalParams(winType);
		var window_name = '';

		if ( typeof params != 'object' ) {
			params = new Object();
		}

		switch ( winType ) {
			case 1:
				params.callback = this.button1Callback;
				params.onHide = this.button1OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				window_name = 'swPersonCardHistoryWindow';
			break;

			case 2:
				if (!Ext.isEmpty(this.getFieldValue('PersonEncrypHIV_Encryp'))) {
					return false;
				}
                var allow_open = 1;
                var ownerCur = this.ownerCt;
                if( typeof ownerCur != "undefined" ) {
                    while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
                        ownerCur = ownerCur.ownerCt;
                    }
                    if (typeof ownerCur.checkRole == 'function') {
                        if (ownerCur.readOnly || (ownerCur.action && ownerCur.action == 'view')) {
                            allow_open = 0;
                        }
                    }
                }
                if(allow_open == 1)
                {
                    params.action = 'edit';
                    params.callback = this.button2Callback;
                    params.onClose = this.button2OnHide;
                    window_name = 'swPersonEditWindow';
                }
                else
                    return false;
			break;

			case 3:
				params.callback = this.button3Callback;
				params.onHide = this.button3OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonCureHistoryWindow';
			break;

			case 4:
				params.callback = this.button4Callback;
				params.onHide = this.button4OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonPrivilegeViewWindow';
			break;

			case 5:
				params.callback = this.button5Callback;
				params.onHide = this.button5OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonDispHistoryWindow';
			break;

			default:
				return false;
			break;
		}

		params.Person_id = this.personId;
		params.Server_id = this.serverId;

		if ( getWnd(window_name).isVisible() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: langs('Окно уже открыто'),
				title: ERR_WND_TIT
			});

			return false;
		}

		getWnd(window_name).show(params);
	},
	personId: null,
	serverId: null,
	setParams: function(params) {
		if ( typeof params != 'object' ) {
			return false;
		}

		this.personId = params.Person_id;
		this.serverId = params.Server_id;
	},
	setReadOnly: function (is_read_only)
	{
		this.ButtonPanel.items.each(function(item, index) {
			if (isMseDepers()) {
				item.disable();
			} else {
				item.enable();
			}
		});
			
		if (is_read_only || isMseDepers()) {
			this.readOnly = true;
			this.ButtonPanel.items.items[1].disable();
		} else {
			this.readOnly = false;
			this.ButtonPanel.items.items[1].enable();
		}
	},
	forReceptCommonAstra: false,
	initComponent: function() {
		var me = this;		

		me.fields = [
			{name: 'Person_id'},
			{name: 'Server_pid'},
			{name: 'Document_begDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Document_Num'},
			{name: 'Document_Ser'},
			{name: 'KLAreaType_id'},
			{name: 'Lpu_Nick'},
			{name: 'Lpu_id'},
			{name: 'LpuRegion_Name'},
			{name: 'OrgDep_Name'},
			{name: 'OrgSmo_Name'},
			{name: 'Person_Age'},
			{name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'PersonCard_begDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'PersonEvn_id'},
			{name: 'Server_id'},
			{name: 'Person_Firname'},
			{name: 'Person_Job'},
			{name: 'Person_PAddress'},
			{name: 'PAddress_Address'},
			{name: 'Person_Phone'},
			{name: 'JobOrg_id'},
			{name: 'Person_Post'},
			{name: 'Person_RAddress'},
			{name: 'RAddress_Address'},
			{name: 'Person_Secname'},
			{name: 'Person_Snils'},
			{name: 'Person_Inn'},
			{name: 'Person_Surname'},
			{name: 'Person_EdNum'},
			{name: 'SurNameLetter'},
			{name: 'Polis_begDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Polis_endDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Polis_Num'},
			{name: 'Polis_Ser'},
			{name: 'OmsSprTerr_id'},
			{name: 'OmsSprTerr_Code'},
			{name: 'Sex_Code'},
			{name: 'Sex_id'},
			{name: 'SocStatus_id'},
			{name: 'Sex_Name'},
			{name: 'SocStatus_Name'},
			{name: 'Person_deadDT', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Person_closeDT', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'PersonCloseCause_id'},
			{name: 'Person_IsDead'},
			{name: 'Person_IsBDZ'},
			{name: 'PersonEncrypHIV_Encryp'},
			{name: 'Person_IsUnknown', type: 'int'},
			{name: 'Person_IsAnonym', type: 'int'},
			{name: 'PersonChild_id'}
		];

		me.additionalFields.forEach(function(item) {
			me.fields.push({name: item});
		});
		
		var cur_tpl = new Ext6.XTemplate(
			'<tpl for=".">',
				'<tpl if="isMseDepers()">', // Деперсонализация данных пациента для МСЭ
				'<div>ФИО: <font style="color: blue; font-weight: bold;">***</font> Д/р: <font style="color: blue;">***</font> Пол: <font style="color: blue;">{Sex_Name}</font> {[(String(values.Person_deadDT) != "" ? "&nbsp;Дата смерти: <font color=red>" + String(Ext.util.Format.date(values.Person_deadDT, "d.m.Y")) + "</font>" : "&nbsp;")]} {[(String(values.Person_closeDT) != "" ? "&nbsp;Дата закрытия: <font color=red>" + String(Ext.util.Format.date(values.Person_closeDT, "d.m.Y")) + "</font>" : "&nbsp;")]}</div>',
				'<div>Соц. статус: <font style="color: blue;">{SocStatus_Name}</font> СНИЛС: <font style="color: blue;">***</font></div>',
				'<div>Регистрация: <font style="color: blue;">***</font></div>',
				'<div>Проживает: <font style="color: blue;">***</font></div>',
				'<div>Телефон: <font style="color: blue;">***</font></div>',
				'<div>Полис: <font style="color: blue;">***</font> Выдан: <font style="color: blue;">***</font>. Закрыт: <font style="color: blue;">***</font></div>',
				'<div>Документ: <font style="color: blue;">***</font> Выдан: <font style="color: blue;">***</font></div>',
				'<div>Работа: <font style="color: blue;">***</font></div>',
				'<div>МО: <font style="color: blue;">{Lpu_Nick}</font> Участок: <font style="color: blue;">{LpuRegion_Name}</font> Дата прикрепления: <font style="color: blue;">{[Ext.util.Format.date(values.PersonCard_begDate, "d.m.Y")]}</font></div>',
				'<tpl elseif="this.allowPersonEncrypHIV() == true">',
				'<div>Шифр: <font style="color: blue;">{PersonEncrypHIV_Encryp}</font></div>',
				'<tpl else>',
				'<div>ФИО: <font style="color: blue; font-weight: bold;">{Person_Surname} {Person_Firname} {Person_Secname}</font> Д/р: <font style="color: blue;">{[Ext.util.Format.date(values.Person_Birthday, "d.m.Y")]}</font> Пол: <font style="color: blue;">{Sex_Name}</font> {[(String(values.Person_deadDT) != "" ? "&nbsp;Дата смерти: <font color=red>" + String(Ext.util.Format.date(values.Person_deadDT, "d.m.Y")) + "</font>" : "&nbsp;")]} {[(String(values.Person_closeDT) != "" ? "&nbsp;Дата закрытия: <font color=red>" + String(Ext.util.Format.date(values.Person_closeDT, "d.m.Y")) + "</font>" : "&nbsp;")]}</div>',
				'<div>Соц. статус: <font style="color: blue;">{SocStatus_Name}</font> СНИЛС: <font style="color: blue;">{[snilsRenderer(values.Person_Snils)]}</font></div>',
				'<div>Регистрация: <font style="color: blue;">{Person_RAddress}</font></div>',
				'<div>Проживает: <font style="color: blue;">{Person_PAddress}</font></div>',
				'<div>Телефон: <font style="color: blue;">{Person_Phone}</font></div>',
				'<div>Полис: <font style="color: blue;">{Polis_Ser} {Polis_Num}</font> Выдан: <font style="color: blue;">{[Ext.util.Format.date(values.Polis_begDate, "d.m.Y")]}, {OrgSmo_Name}</font>. Закрыт: <font style="color: blue;">{[Ext.util.Format.date(values.Polis_endDate, "d.m.Y")]}</font></div>',
				'<div>Документ: <font style="color: blue;">{Document_Ser} {Document_Num}</font> Выдан: <font style="color: blue;">{[Ext.util.Format.date(values.Document_begDate, "d.m.Y")]}, {OrgDep_Name}</font></div>',
				'<div>Работа: <font style="color: blue;">{Person_Job}</font> Должность: <font style="color: blue;">{Person_Post}</font></div>',
				'<div>МО: <font style="color: blue;">{Lpu_Nick}</font> Участок: <font style="color: blue;">{LpuRegion_Name}</font> Дата прикрепления: <font style="color: blue;">{[Ext.util.Format.date(values.PersonCard_begDate, "d.m.Y")]}</font></div>',
				'</tpl>',
				'</tpl>',
				{
					allowPersonEncrypHIV: function () {
						return (!Ext.isEmpty(me.getFieldValue('PersonEncrypHIV_Encryp')));
					}
				}
		);
		if(this.forReceptCommonAstra)
		{
			cur_tpl = new Ext.XTemplate(
				'<tpl for=".">',
				'<div>&nbsp;&nbsp;&nbsp;<font style="color: blue; font-weight: bold;">{Person_Firname} {Person_Secname} {SurNameLetter} </font></div>' ,
				'<div>&nbsp;&nbsp;&nbsp;Д/р: <font style="color: blue;">{[Ext.util.Format.date(values.Person_Birthday, "d.m.Y")]}</font> г.р. </div>' ,
				'<div>&nbsp;&nbsp;&nbsp;СНИЛС: <font style="color: blue;">{[snilsRenderer(values.Person_Snils)]}</font></div>' ,
				'<div>&nbsp;&nbsp;&nbsp;Полис: <font style="color: blue;">{Polis_Ser} {Polis_Num}</font>&nbsp; ЕНП: <font style="color: blue;">{Person_EdNum}</font></div>',
				'</tpl>',
				{
					allowPersonEncrypHIV: function () {
						return (!Ext.isEmpty(me.getFieldValue('PersonEncrypHIV_Encryp')));
					}
				}
			);
		}
		
		this.DataView = new Ext.DataView({
			border: false,
			frame: false,
			itemSelector: 'div',
			region: 'center',
			store: new Ext.data.JsonStore({
				autoLoad: false,
				baseParams: {
					mode: 'PersonInformationPanel',
					additionalFields: Ext.util.JSON.encode(me.additionalFields)
				},
				fields: me.fields,
				url: '/?c=Common&m=loadPersonData'
			}),
			tpl: cur_tpl
		});

		this.ButtonPanel = new Ext.Panel({
			style: 'height:105px!important;',
			bodyStyle: 'background-color: transparent;',
			border: false,
			defaults: {
				xtype: 'button',
				minWidth: '180'
			},
			items: [{
				disabled: false,
				handler: function() {
					me.panelButtonClick(1);
				},
				text: BTN_PERSCARD,
				iconCls: 'pers-card16',
				tooltip: BTN_PERSCARD_TIP
			}, {
				disabled: false,
				handler: function() {
					me.panelButtonClick(2);
				},
				text: BTN_PERSEDIT,
				iconCls: 'edit16',
				tooltip: BTN_PERSEDIT_TIP
			}, {
				disabled: false,
				handler: function() {
					me.panelButtonClick(3);
				},
				text: BTN_PERSCUREHIST,
				iconCls: 'pers-curehist16',
				tooltip: BTN_PERSCUREHIST_TIP
			}, {
				disabled: false,
				handler: function() {
					me.panelButtonClick(4);
				},
				text: BTN_PERSPRIV,
				iconCls: 'pers-priv16',
				tooltip: BTN_PERSPRIV_TIP
			}, {
				disabled: false,
				handler: function() {
					me.panelButtonClick(5);
				},
				text: BTN_PERSDISP,
				iconCls: 'pers-disp16',
				tooltip: BTN_PERSDISP_TIP
			}],
			region: 'east',
			width: 180
		});
		Ext.apply(this, {
			items: [ this.DataView, this.ButtonPanel]
		});
		sw.Promed.PersonInformationPanel.superclass.initComponent.apply(this, arguments);
	}
});

sw.Promed.PersonInfoPanelView = Ext.extend(sw.Promed.Panel,
    {
        border: false,
        getFieldValue: function(field) {
            var result = '';
            if (this.DataView.getStore().getAt(0))
                result = this.DataView.getStore().getAt(0).get(field);

            return result;
        },
        layout: 'form',
        listeners: {
            'render': function(panel) {
                if (panel.header)
                {
                    panel.header.on({
                        'click': {
                            fn: this.toggleCollapse,
                            scope: panel
                        }
                    });
                }
            },
            'resize': function (p,nW, nH, oW, oH){
                p.doLayout();
            },
            'maximize': function (p,nW, nH, oW, oH){
                p.doLayout();
            }
        },
        load: function(params) {
            var callback_param = function() {
                if ( typeof params.callback == 'function' ) {
                    params.callback();
                }

                this.doLayout();
            }.createDelegate(this);

            this.personId = params.Person_id;
            this.serverId = params.Server_id;
            this.personEvnId = params.PersonEvn_id;
            this.Evn_setDT = params.Evn_setDT;
			if(getWnd('swWorkPlaceMZSpecWindow').isVisible()) //https://redmine.swan.perm.ru/issues/92555 Эта панель дохрена где используется, а для АРМа МЗ ее надо скрыть, поэтому чем перелопачивать туеву хучу форм, сделал такой вот финт ушами
				this.disable();
			else
				this.enable();
            // если персон не сменился после последней загрузки и загружена полная информация или схлапывание панели
            if ( typeof this.loadedPerson_id != undefined && this.loadedPerson_id == params.personId &&
                typeof this.loadedServer_id != undefined && this.loadedServer_id == params.serverId &&
                typeof this.loadedPersonEvn_id != undefined && this.loadedPersonEvn_id == params.personEvn &&
                this.loadedFull && params.onExpand )
                return true;

            params.LoadShort = false;
            this.loadedFull = true;

            this.loadedPerson_id = this.personId;
            this.loadedPersonEvn_id = this.personEvnId;
            this.loadedServer_id = this.serverId;
            this.loadedEvn_setDT = this.Evn_setDT;

            this.DataView.getStore().removeAll();
            this.DataView.getStore().load({
                params: params,
                callback: callback_param
            });

            this.doLayout();
        },
        personId: null,
        serverId: null,
        personEvnId: null,
        Evn_setDT:null,
        setParams: function(params) {
            if ( typeof params != 'object' ) {
                return false;
            }

            this.personId = params.Person_id;
            this.serverId = params.Server_id;
            this.personEvnId = params.PersonEvn_id;
            this.Evn_setDT = params.Evn_setDT;
        },
        initComponent: function()
        {
            var PolisInnField = '<div style="padding-left: 10px;">Полис: <font style="color: blue;">{Polis_Ser} {Polis_Num}</font> Выдан: <font style="color: blue;">{[Ext.util.Format.date(values.Polis_begDate, "d.m.Y")]}, {OrgSmo_Name}</font>. Закрыт: <font style="color: blue;">{[Ext.util.Format.date(values.Polis_endDate, "d.m.Y")]}</font></div>';

            this.DataView = new Ext.DataView(
                {
                    border: false,
                    frame: false,
                    autoScroll: true,
                    itemSelector: 'div',
                    region: 'center',
                    store: new Ext.data.JsonStore(
                        {
                            autoLoad: false,
							baseParams: {
								mode: 'PersonInfoPanelView'
							},
                            fields:
                                [
                                    {name: 'Person_id'},
                                    {name: 'Server_id'},
                                    {name: 'Document_begDate', dateFormat: 'd.m.Y', type: 'date'},
                                    {name: 'Document_Num'},
                                    {name: 'Document_Ser'},
                                    {name: 'KLAreaType_id'},
                                    {name: 'Lpu_Nick'},
                                    {name: 'Lpu_id'},
                                    {name: 'LpuRegion_Name'},
                                    {name: 'OrgDep_Name'},
                                    {name: 'OrgSmo_Name'},
                                    {name: 'Person_Age'},
                                    {name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date'},
                                    {name: 'PersonCard_begDate', dateFormat: 'd.m.Y', type: 'date'},
                                    {name: 'PersonEvn_id'},
                                    {name: 'Person_Firname'},
                                    {name: 'Person_Job'},
                                    {name: 'Person_PAddress'},
                                    {name: 'Person_Phone'},
                                    {name: 'JobOrg_id'},
                                    {name: 'Person_Post'},
                                    {name: 'Person_RAddress'},
                                    {name: 'Person_Secname'},
                                    {name: 'Person_Snils'},
                                    {name: 'Person_Inn'},
                                    {name: 'Person_Surname'},
                                    {name: 'Polis_begDate', dateFormat: 'd.m.Y', type: 'date'},
                                    {name: 'Polis_endDate', dateFormat: 'd.m.Y', type: 'date'},
                                    {name: 'Polis_Num'},
                                    {name: 'Polis_Ser'},
                                    {name: 'OmsSprTerr_id'},
                                    {name: 'OmsSprTerr_Code'},
                                    {name: 'Sex_Code'},
                                    {name: 'Sex_id'},
                                    {name: 'SocStatus_id'},
                                    {name: 'Sex_Name'},
                                    {name: 'SocStatus_Name'},
                                    {name: 'Person_deadDT', dateFormat: 'd.m.Y', type: 'date'},
                                    {name: 'Person_closeDT', dateFormat: 'd.m.Y', type: 'date'},
                                    {name: 'PersonCloseCause_id'},
                                    {name: 'Person_IsDead'},
                                    {name: 'Person_IsBDZ'},
                                    {name: 'PrivilegeType_id'},
                                    {name: 'PrivilegeType_Name'},
									{name: 'PersonChild_id'}
                                ],
                            url: '/?c=Common&m=loadPersonData'
                        }),
                    tpl: new Ext.XTemplate(
                        '<tpl for=".">',
                        '<div style="padding-left: 10px;">ФИО: <font style="color: blue; font-weight: bold;">{Person_Surname} {Person_Firname} {Person_Secname}</font> Д/р: <font style="color: blue;">{[Ext.util.Format.date(values.Person_Birthday, "d.m.Y")]}</font> Пол: <font style="color: blue;">{Sex_Name}</font> {[(String(values.Person_deadDT) != "" ? "&nbsp;Дата смерти: <font color=red>" + String(Ext.util.Format.date(values.Person_deadDT, "d.m.Y")) + "</font>" : "&nbsp;")]} {[(String(values.Person_closeDT) != "" ? "&nbsp;Дата закрытия: <font color=red>" + String(Ext.util.Format.date(values.Person_closeDT, "d.m.Y")) + "</font>" : "&nbsp;")]}</div>',
                        '<div style="padding-left: 10px;">Соц. статус: <font style="color: blue;">{SocStatus_Name}</font> СНИЛС: <font style="color: blue;">{[snilsRenderer(values.Person_Snils)]}</font></div>' +
                        '<div style="padding-left: 10px;">Инвалидность: <font style="color: blue;">{PrivilegeType_Name}</font></div>',
                        '<div style="padding-left: 10px;">Регистрация: <font style="color: blue;">{Person_RAddress}</font></div>',
                        '<div style="padding-left: 10px;">Проживает: <font style="color: blue;">{Person_PAddress}</font></div>',
                        '<div style="padding-left: 10px;">Телефон: <font style="color: blue;">{Person_Phone}</font></div>',
                        PolisInnField,
                        '<div style="padding-left: 10px;">Документ: <font style="color: blue;">{Document_Ser} {Document_Num}</font> Выдан: <font style="color: blue;">{[Ext.util.Format.date(values.Document_begDate, "d.m.Y")]}, {OrgDep_Name}</font></div>',
                        '<div style="padding-left: 10px;">Работа: <font style="color: blue;">{Person_Job}</font> Должность: <font style="color: blue;">{Person_Post}</font></div>',
                        '<div style="padding-left: 10px;">МО: <font style="color: blue;">{Lpu_Nick}</font> Участок: <font style="color: blue;">{LpuRegion_Name}</font> Дата прикрепления: <font style="color: blue;">{[Ext.util.Format.date(values.PersonCard_begDate, "d.m.Y")]}</font></div>',
                        '</tpl>'
                    )
                });
            Ext.apply(this,
                {
                    border: true,
                    style: 'height: 180px;',
                    layout: 'form',
                    listeners:
                    {
                        resize: function (p,nW, nH, oW, oH)
                        {
                            p.doLayout();
                        },
                        maximize: function (p,nW, nH, oW, oH)
                        {
                            p.doLayout();
                        }
                    },
                    items:
                    [
                        this.DataView
                    ]
                });
            sw.Promed.PersonInfoPanelView.superclass.initComponent.apply(this, arguments);
        }
    });

sw.Promed.PersonInfoPanel = Ext.extend(sw.Promed.Panel,
{
	additionalFields: [],
	border: false,
	readOnly: false,
	button1Callback: Ext.emptyFn,
	button2Callback: Ext.emptyFn,
	button3Callback: Ext.emptyFn,
	button4Callback: Ext.emptyFn,
	button5Callback: Ext.emptyFn,
	button1OnHide: Ext.emptyFn,
	button2OnHide: Ext.emptyFn,
	button3OnHide: Ext.emptyFn,
	button4OnHide: Ext.emptyFn,
	button5OnHide: Ext.emptyFn,
	collectAdditionalParams: Ext.emptyFn,
	setFieldValue: function(field, value) {
		if (this.DataView.getStore().getAt(0))
			this.DataView.getStore().getAt(0).set(field, value);
	},
	getFieldValue: function(field) {
		var result = '';
		if (this.DataView.getStore().getAt(0))
			result = this.DataView.getStore().getAt(0).get(field);
		
		return result;
	},
	layout: 'form',
	title: langs('Загрузка...'),
	listeners: {
		'beforeexpand': function(a,b) {
			if(this.showlabel) {
				this.showlabel=false;
				return false;
			}
			else return true;
		},
		'expand': function(p) {
			p.load({
				onExpand: true,
				PersonEvn_id: p.personEvnId,
				Person_id: p.personId,
				Server_id: p.serverId,
				Evn_setDT:p.Evn_setDT
			});
		},
		'render': function(panel) {
			if (panel.header)
			{
				panel.header.on({
					'click': {
						fn: this.toggleCollapse,
						scope: panel
					}
				});
			}
		},
		'resize': function (p,nW, nH, oW, oH){
			p.doLayout();
		},
		'maximize': function (p,nW, nH, oW, oH){
			p.doLayout();
		}
	},
	load: function(params) {
		var callback_param = function () {
			if (typeof params.callback == 'function') {
				params.callback();
			}
			
			// чтобы не спалиться, если форма будет тянуть данные отсюда
			if (isMseDepers()) {
				this.setFieldValue('Person_Firname', '***');
				this.setFieldValue('Person_Secname', '***');
				this.setFieldValue('Person_Surname', '***');
			}
			
			this.setReadOnly(this.readOnly);
			this.doLayout();
		}.createDelegate(this);
		this.personId = params.Person_id;
		this.serverId = params.Server_id;
		this.personEvnId = params.PersonEvn_id;
		this.Evn_setDT = params.Evn_setDT;
		if (this.isLis)
			params.isLis = this.isLis;
		// если персон не сменился после последней загрузки и загружена полная информация или схлапывание панели
		if ( typeof this.loadedPerson_id != undefined && this.loadedPerson_id == params.personId &&
		typeof this.loadedServer_id != undefined && this.loadedServer_id == params.serverId &&
		typeof this.loadedPersonEvn_id != undefined && this.loadedPersonEvn_id == params.personEvn &&
		this.loadedFull && params.onExpand )
			return true;
		
		params.LoadShort = false;
		this.loadedFull = true;
				
		/*if ( params.onExpand )
		{
			params.LoadShort = false;
			this.loadedFull = true;
		}
		else if ( this.collapsed )
		{
			params.LoadShort = true;
			this.loadedFull = false;
		}	*/	
		
		this.loadedPerson_id = this.personId;
		this.loadedPersonEvn_id = this.personEvnId;
		this.loadedServer_id = this.serverId;
		this.loadedEvn_setDT = this.Evn_setDT;
		
		this.DataView.getStore().removeAll();
		this.DataView.getStore().load({
			params: params,
			callback: callback_param
		});

		this.setReadOnly(false);

		if (haveArmType('spec_mz')) {
			this.setReadOnly(true);
		} else {
			// ищем родительское окно, если есть то устанавливаем возможность редактировать пользователя в зависимости от action/readOnly в родительском окне.
			var ownerCur = this.ownerCt;
			if (typeof ownerCur != "undefined") {
				while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
					ownerCur = ownerCur.ownerCt;
				}
				if (typeof ownerCur.checkRole == 'function') {
					if (ownerCur.readOnly || (ownerCur.action && ownerCur.action == 'view')) {
						this.setReadOnly(true);
					}
				}
			}
		}

		this.doLayout();
	},
	panelButtonClick: function(winType) {
		var params = this.collectAdditionalParams(winType);
		var window_name = '';

		if ( typeof params != 'object' ) {
			params = new Object();
		}

		switch ( winType ) {
			case 1:
				params.callback = this.button1Callback;
				params.onHide = this.button1OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonCardHistoryWindow';
			break;

			case 2:
                var allow_open = 1;
                var ownerCur = this.ownerCt;
                if( typeof ownerCur != "undefined" ) {
                    while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
                        ownerCur = ownerCur.ownerCt;
                    }
                    if (typeof ownerCur.checkRole == 'function') {
                        if (ownerCur.readOnly || (ownerCur.action && ownerCur.action == 'view')) {
                            allow_open = 0;
                        }
                    }
                }
                if(allow_open == 1)
                {
                    params.action = 'edit';
                    params.callback = this.button2Callback;
                    params.onClose = this.button2OnHide;
                    window_name = 'swPersonEditWindow';
                }
                else
                    return false;
			break;

			case 3:
				params.callback = this.button3Callback;
				params.onHide = this.button3OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonCureHistoryWindow';
			break;

			case 4:
				params.callback = this.button4Callback;
				params.onHide = this.button4OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonPrivilegeViewWindow';
			break;

			case 5:
				params.callback = this.button5Callback;
				params.onHide = this.button5OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonDispHistoryWindow';
			break;

			default:
				return false;
			break;
		}

		params.Person_id = this.personId;
		params.Server_id = this.serverId;
		params.PersonEvn_id = this.personEvnId;
		params.Evn_setDT = this.Evn_setDT;

		if ( getWnd(window_name).isVisible() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: langs('Окно уже открыто'),
				title: ERR_WND_TIT
			});

			return false;
		}

		getWnd(window_name).show(params);
	},
	personId: null,
	serverId: null,
	personEvnId: null,
	Evn_setDT:null,
	setParams: function(params) {
		if ( typeof params != 'object' ) {
			return false;
		}

		this.personId = params.Person_id;
		this.serverId = params.Server_id;
		this.personEvnId = params.PersonEvn_id;
		this.Evn_setDT = params.Evn_setDT;
	},
	setPersonChangeParams: function(params) {
		if (getRegionNick() == 'ufa' && !params.isEvnPS) { // для Уфы только в КВС
			this.clearPersonChangeParams();
			return false;
		}
		this.personChangeParams = new Object();

		if ( typeof params != 'object' ) {
			return false;
		}

		this.personChangeParams.callback = params.callback;
		this.personChangeParams.CmpCallCard_id = params.CmpCallCard_id;
		this.personChangeParams.Evn_id = params.Evn_id;

		return true;
	},
	clearPersonChangeParams: function() {
		this.personChangeParams = new Object();

		this.personChangeParams.callback = Ext.emptyFn;
		this.personChangeParams.CmpCallCard_id = null;
		this.personChangeParams.Evn_id = null;

		return true;
	},
	changePerson: function() {
		if ( !(getRegionNick().inlist(['perm', 'ufa', 'buryatiya'])) ) {
			return false;
		}
		else if ( !this.personChangeParams.CmpCallCard_id && !this.personChangeParams.Evn_id ) {
			return false;
		}

		var params = {
			 CmpCallCard_id: this.personChangeParams.CmpCallCard_id
			,Evn_id: this.personChangeParams.Evn_id
		}

		if ( getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
			return false;
		}

		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				params.Person_id = person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id = person_data.Server_id;
				params.Person_SurName = person_data.Person_Surname;
				params.Person_FirName = person_data.Person_Firname;
				params.Person_SecName = person_data.Person_Secname;

				this.setAnotherPersonForDocument(params);
			}.createDelegate(this),
			personFirname: this.getFieldValue('Person_Firname'),
			personSecname: this.getFieldValue('Person_Secname'),
			personSurname: this.getFieldValue('Person_Surname'),
			searchMode: 'all'
		});
	},
	setAnotherPersonForDocument: function(params) {
		var loadMask = new Ext.LoadMask(getWnd('swPersonSearchWindow').getEl(), { msg: "Переоформление документа на другого человека..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при переоформлении документа на другого человека'));
					}
					else if ( response_obj.Alert_Msg ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									switch ( response_obj.Alert_Code ) {
										case 1:
											params.allowEvnStickTransfer = 2;
										case 2:
											params.ignoreAgeFioCheck = 2;
										break;
									}

									this.setAnotherPersonForDocument(params);
								}
							}.createDelegate(this),
							msg: response_obj.Alert_Msg,
							title: langs('Вопрос')
						});
					} else {
						getWnd('swPersonSearchWindow').hide();
                        var info_msg = langs('Документ успешно переоформлен на другого человека');
                        if (response_obj.Info_Msg) {
                            info_msg += '<br>' + response_obj.Info_Msg;
                        }
						sw.swMsg.alert(langs('Сообщение'), info_msg, function() {
							this.personChangeParams.callback({
								 CmpCallCard_id: response_obj.CmpCallCard_id
								,Evn_id: response_obj.Evn_id
								,Person_id: params.Person_id
								,PersonEvn_id: params.PersonEvn_id
								,Server_id: params.Server_id
								,Person_SurName: params.Person_SurName
								,Person_FirName: params.Person_FirName
								,Person_SecName: params.Person_SecName
							});
						}.createDelegate(this));
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При переоформлении документа на другого человека произошли ошибки'));
				}
			}.createDelegate(this),
			params: params,
			url: C_CHANGEPERSONFORDOC
		});
	},
	setPersonTitle: function()
	{
		if (Ext.isEmpty(this.personId)) {
			this.setTitle('...');
			return;
		}

		var personChangeIsAvailable = (typeof this.personChangeParams == 'object' && (this.personChangeParams.CmpCallCard_id > 0 || this.personChangeParams.Evn_id > 0));
		var PersonMainInfo = '';
		if (isMseDepers()) {
			PersonMainInfo = 'ИД пациента: ' + this.personId;
		} else if (!Ext.isEmpty(this.getFieldValue('PersonEncrypHIV_Encryp'))) {
			PersonMainInfo = this.getFieldValue('PersonEncrypHIV_Encryp');
		} else {
			if (this.getFieldValue('PersonQuarantine_IsOn') == 'true') {
				PersonMainInfo = '<font color="red">'+this.getFieldValue('Person_Surname')+' '+
					this.getFieldValue('Person_Firname')+' '+this.getFieldValue('Person_Secname')+', '+
					Ext.util.Format.date(this.getFieldValue('Person_Birthday'), "d.m.Y")+' '+
					("(Возраст:")+' '+this.getFieldValue('Person_Age')+''+(")")+'</font>';
			} else {
				PersonMainInfo = this.getFieldValue('Person_Surname')+' '+
					this.getFieldValue('Person_Firname')+' '+this.getFieldValue('Person_Secname')+', '+
					Ext.util.Format.date(this.getFieldValue('Person_Birthday'), "d.m.Y")+' '+
					("(Возраст:")+' '+this.getFieldValue('Person_Age')+''+(")");
			}
			if (getRegionNick() == 'kz') {
				PersonMainInfo += (this.getFieldValue('Person_Inn').length>0)?' / ИИН '+this.getFieldValue('Person_Inn'):'';
			} else {
				PersonMainInfo += (this.getFieldValue('Polis_Ser').length>0)?' / полис '+this.getFieldValue('Polis_Ser')+' '+this.getFieldValue('Polis_Num'):'';
			}
		}
		this.labels='';
		var labelhtml='';
		if(this.id=='PEMK_PersonInfoFrame') {
			var labels=this.getFieldValue('PersLabels');
			if(labels) {
				this.labels = labels.split('|').join('<br>');
				labelhtml = '<span id="'+this.id+'-person-labels" '
				+'onClick="Ext.getCmp(\''+this.id+'\').showLabels(this);">'
				+'<b class="person-labels"></b></span>';
			}
		}
		
		this.setTitle('<div class="x-panel-collapsed-title"><span>'+PersonMainInfo+
			(String(this.getFieldValue('Person_deadDT')) != '' ? ' Дата смерти: <font color=red>' + Ext.util.Format.date(this.getFieldValue('Person_deadDT'), "d.m.Y") + '</font>' : '' ) +
			(String(this.getFieldValue('Person_closeDT')) != '' ? ' Дата закрытия: <font color=red>' + Ext.util.Format.date(this.getFieldValue('Person_closeDT'), "d.m.Y") + '</font>' : '' ) +
			(this.getFieldValue('PersonQuarantine_IsOn') == 'true' ? ' Дата начала карантина COVID-19: <font color=red>' + this.getFieldValue('PersonQuarantine_begDT') + '</font>' : '') +
			(personChangeIsAvailable && getRegionNick().inlist(['perm', 'ufa', 'buryatiya']) ? ' <a onclick="Ext.getCmp(\'' + this.id + '\').changePerson(); return false;" style="font-weight: bold; color: blue; text-decoration: underline;" onMouseover="this.style.cursor=\'pointer\'">СМЕНИТЬ ПАЦИЕНТА</a>' : '') + '</span>'+labelhtml+'</div>');
	},
	showLabels: function(e) {
		this.showlabel=true;
		if(!Ext.isEmpty(this.labels)) {
			if(!this.labeltip)
			this.labeltip = Ext6.create('Ext6.tip.ToolTip', {
				html: '',
				autoHide: true,
				closable: false
			});
			this.labeltip.setHtml(this.labels);
			var el_labels = document.getElementById(this.id+'-person-labels');
			
			if(el_labels) {
				var rect = e.getBoundingClientRect();
				this.labeltip.showAt([rect.x+rect.width, rect.y+rect.height]);
			}
		} else this.showlabel=false;
	},
	setReadOnly: function (is_read_only)
	{
		if (!Ext.isEmpty(this.getFieldValue('PersonEncrypHIV_Encryp'))) {
			is_read_only = true;
		} 
		if (is_read_only) {
			this.readOnly = true;
			this.ButtonPanel.items.items[1].disable();
		} else {
			this.readOnly = false;
			this.ButtonPanel.items.items[1].enable();
		}
	},
	initComponent: function() 
	{
		var comp = this,
			regNick = getRegionNick(),

			// нужна ли деперсонализация данных:
			depers = isMseDepers(),

			// ИНН с деперсонализацией:
			InnField =
				'<div style="padding-left: 25px;">' +
				(regNick == 'kz' ? 'ИИН' : 'ИНН') +
				': <font style="color: blue;">' +
				(depers ? '***' : '{Person_Inn}') +
				'</font></div>',

			// Полис c деперсонализацией:
			PolisField =
				'<div style="padding-left: 25px;">Полис: <font style="color: blue;">'+
				(depers ?
					'***' :
					'{Polis_Ser} {Polis_Num}') +
				'</font> Выдан: <font style="color: blue;">' +
				(depers ?
					'***' :
					'{[Ext.util.Format.date(values.Polis_begDate, "d.m.Y")]}, {OrgSmo_Name}') +
				'</font>. Закрыт: <font style="color: blue;">' +
				(depers ?
					'***' :
					'{[Ext.util.Format.date(values.Polis_endDate, "d.m.Y")]}') +
				'</font></div>',

			// СНИЛС с деперсонализацией, в Казахстане отсутствует:
			Snils =
				(regNick == 'kz' ?
					'' :
					(' СНИЛС: <font style="color: blue;">' +
						(depers ?
							'***' :
							'{[snilsRenderer(values.Person_Snils)]}') +
						'</font>')),

			// Семейное положение с деперсонализацией:
			FamilyStatusField =
				'<div style="padding-left: 25px;">Семейное положение: <font style="color: blue;">' +
				(depers ? '***' : '{FamilyStatus_Name}') +
				'</font></div>';

		if (regNick == 'ufa') {
			comp.additionalFields.push('PrivilegeType_id');
			comp.additionalFields.push('PrivilegeType_Name');
		}
		this.DataView = new Ext.DataView(
		{
			border: false,
			frame: false,
			autoScroll: true,
			itemSelector: 'div',
			region: 'center',
			store: new Ext.data.JsonStore(
			{
				autoLoad: false,
				baseParams: {
					mode: 'PersonInfoPanel',
					additionalFields: Ext.util.JSON.encode(comp.additionalFields)
				},
				fields: 
				[
					{name: 'Person_id'},
					{name: 'Server_id'},
					{name: 'Document_begDate', dateFormat: 'd.m.Y', type: 'date'},
					{name: 'Document_Num'},
					{name: 'Document_Ser'},
					{name: 'KLAreaType_id'},
					{name: 'Lpu_Nick'},
					{name: 'Lpu_id'},
					{name: 'LpuRegion_Name'},
					{name: 'OrgDep_Name'},
					{name: 'OrgSmo_Name'},
					{name: 'Person_Age'},
					{name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date'},
					{name: 'PersonCard_id'},
					{name: 'PersonCard_begDate', dateFormat: 'd.m.Y', type: 'date'},
					{name: 'PersonEvn_id'},
					{name: 'Person_Firname'},
					{name: 'Person_Job'},
					{name: 'Person_PAddress'},
					{name: 'Person_Phone'},
					{name: 'JobOrg_id'},
					{name: 'Person_Post'},
					{name: 'Person_RAddress'},
					{name: 'Person_Secname'},
					{name: 'Person_Snils'},
					{name: 'Person_Inn'},
					{name: 'Person_Surname'},
					{name: 'Polis_begDate', dateFormat: 'd.m.Y', type: 'date'},
					{name: 'Polis_endDate', dateFormat: 'd.m.Y', type: 'date'},
					{name: 'Polis_Num'},
					{name: 'Polis_Ser'},
					{name: 'OmsSprTerr_id'},
					{name: 'OmsSprTerr_Code'},
					{name: 'KLRgn_id'},
					{name: 'Sex_Code'},
					{name: 'Sex_id'},
					{name: 'Sex_Name'},
					{name: 'SocStatus_id'},
					{name: 'SocStatus_Name'},
					{name: 'FamilyStatus_id'},
					{name: 'FamilyStatus_Name'},
					{name: 'Person_deadDT', dateFormat: 'd.m.Y', type: 'date'},
					{name: 'Person_closeDT', dateFormat: 'd.m.Y', type: 'date'},
					{name: 'PersonCloseCause_id'},
					{name: 'Person_IsDead'},
					{name: 'Person_IsBDZ'},
					{name: 'Person_IsFedLgot'},
					{name: 'PrivilegeType_id'},
					{name: 'PrivilegeType_Name'},
					{name: 'PersonEncrypHIV_Encryp'},
					{name: 'Person_IsAnonym'},
					{name: 'DeputyPerson_id'},
					{name: 'NewslatterAccept'},
					{name: 'PersLabels'},
					{name: 'PersonChild_id'},
					{name: 'FeedingType_Name'},
					{name: 'PersonQuarantine_IsOn'},
					{name: 'PersonQuarantine_begDT'}
				],
				url: '/?c=Common&m=loadPersonData'
			}),
			tpl: new Ext6.XTemplate(
				'<tpl for=".">',
				'<tpl if="isMseDepers()">', // Деперсонализация данных пациента для МСЭ
				'<div style="padding-left: 25px;">ФИО: <font style="color: blue; font-weight: bold;">***</font> Д/р: <font style="color: blue;">***</font> Пол: <font style="color: blue;">{Sex_Name}</font></div>',
				'<div style="padding-left: 25px;">Соц. статус: <font style="color: blue;">{SocStatus_Name}</font>'+Snils+'</div><tpl if="this.allowShowPrivilegeType(values.PrivilegeType_id) == true"><div style="padding-left: 25px;">Инвалидность: <font style="color: blue;">{PrivilegeType_Name}</font></div></tpl>',
				'<div style="padding-left: 25px;">Регистрация: <font style="color: blue;">***</font></div>',
				'<div style="padding-left: 25px;">Проживает: <font style="color: blue;">***</font></div>',
				'<div style="padding-left: 25px;">Телефон: <font style="color: blue;">***</font></div>',
				InnField,
				PolisField,
				'<div style="padding-left: 25px;">Документ: <font style="color: blue;">***</font> Выдан: <font style="color: blue;">***</font></div>',
				FamilyStatusField,
				'<div style="padding-left: 25px;">Работа: <font style="color: blue;">{Person_Job}</font> Должность: <font style="color: blue;">{Person_Post}</font></div>',
				'<div style="padding-left: 25px;">МО: <font style="color: blue;">{Lpu_Nick}</font> Участок: <font style="color: blue;">{LpuRegion_Name}</font> Дата прикрепления: <font style="color: blue;">{[Ext.util.Format.date(values.PersonCard_begDate, "d.m.Y")]}</font></div>',
				'<div style="padding-left: 25px;">Согласие на получение уведомлений: <font style="color: blue;">{NewslatterAccept}</font></div>',
				'<tpl elseif="this.allowPersonEncrypHIV()">',
				'<div style="padding-left: 25px;">Шифр: <font style="color: blue;">{PersonEncrypHIV_Encryp}</font></div>',
				'<tpl else>',
				'<div style="padding-left: 25px;">ФИО: <font style="color: blue; font-weight: bold;">{Person_Surname} {Person_Firname} {Person_Secname}</font> Д/р: <font style="color: blue;">{[Ext.util.Format.date(values.Person_Birthday, "d.m.Y")]}</font> Пол: <font style="color: blue;">{Sex_Name}</font> {[(String(values.Person_deadDT) != "" ? "&nbsp;Дата смерти: <font color=red>" + String(Ext.util.Format.date(values.Person_deadDT, "d.m.Y")) + "</font>" : "&nbsp;")]} {[(String(values.Person_closeDT) != "" ? "&nbsp;Дата закрытия: <font color=red>" + String(Ext.util.Format.date(values.Person_closeDT, "d.m.Y")) + "</font>" : "&nbsp;")]}</div>',
				'<div style="padding-left: 25px;">Соц. статус: <font style="color: blue;">{SocStatus_Name}</font>'+Snils+'</div><tpl if="this.allowShowPrivilegeType(values.PrivilegeType_id) == true"><div style="padding-left: 25px;">Инвалидность: <font style="color: blue;">{PrivilegeType_Name}</font></div></tpl>',
				'<div style="padding-left: 25px;">Регистрация: <font style="color: blue;">{Person_RAddress}</font></div>',
				'<div style="padding-left: 25px;">Проживает: <font style="color: blue;">{Person_PAddress}</font></div>',
				'<div style="padding-left: 25px;">Телефон: <font style="color: blue;">{Person_Phone}</font></div>',
				InnField,
				PolisField,
				'<div style="padding-left: 25px;">Документ: <font style="color: blue;">{Document_Ser} {Document_Num}</font> Выдан: <font style="color: blue;">{[Ext.util.Format.date(values.Document_begDate, "d.m.Y")]}, {OrgDep_Name}</font></div>',
				FamilyStatusField,
				'<div style="padding-left: 25px;">Работа: <font style="color: blue;">{Person_Job}</font> Должность: <font style="color: blue;">{Person_Post}</font></div>',
				'<div style="padding-left: 25px;">МО: <font style="color: blue;">{Lpu_Nick}</font> Участок: <font style="color: blue;">{LpuRegion_Name}</font> Дата прикрепления: <font style="color: blue;">{[Ext.util.Format.date(values.PersonCard_begDate, "d.m.Y")]}</font></div>',
				'<div style="padding-left: 25px;">Согласие на получение уведомлений: <font style="color: blue;">{NewslatterAccept}</font></div>',
				'<tpl if="this.FeedingTypeHide()">',
				'<div style="padding-left: 25px;">Способ вскармливания: <font style="color: blue;">{FeedingType_Name}</font></div>',
				'</tpl>',
				'</tpl>',
				'</tpl>',
				{
					FeedingTypeHide: function () {
						return(this.getFieldValue('Person_Age') <= 5)
					}.createDelegate(this),
					allowPersonEncrypHIV: function() {
						return (!Ext.isEmpty(this.getFieldValue('PersonEncrypHIV_Encryp')));
					}.createDelegate(this),
					allowShowPrivilegeType: function(PrivilegeType_id) {
						if (
							PrivilegeType_id
							&& PrivilegeType_id.toString().inlist(['81','82','83','84'])
							&& getRegionNick() == 'ufa'
						) {
							return true;
						}
						return false;
					}.createDelegate(this)
				}
			)
		});
		this.ButtonPanel = new Ext.Panel(
		{
			border: false,
			style: 'background-color: transparent;',
			defaults: 
			{
				//minWidth: 130,
				xtype: 'button'
			},
			items: 
			[{
				disabled: isMseDepers(),
				handler: function() {
					this.ownerCt.ownerCt.ownerCt.panelButtonClick(1);
				},
				iconCls: 'pers-card16',
				tooltip: BTN_PERSCARD_TIP
			}, {
				disabled: isMseDepers(),
				handler: function() {
					this.ownerCt.ownerCt.ownerCt.panelButtonClick(2);
				},
				iconCls: 'edit16',
				tooltip: BTN_PERSEDIT_TIP
			}, {
				disabled: isMseDepers(),
				handler: function() {
					this.ownerCt.ownerCt.ownerCt.panelButtonClick(3);
				},
				iconCls: 'pers-curehist16',
				tooltip: BTN_PERSCUREHIST_TIP
			}, {
				disabled: isMseDepers(),
				handler: function() {
					this.ownerCt.ownerCt.ownerCt.panelButtonClick(4);
				},
				iconCls: 'pers-priv16',
				tooltip: BTN_PERSPRIV_TIP
			}, {
				disabled: isMseDepers(),
				handler: function() {
					this.ownerCt.ownerCt.ownerCt.panelButtonClick(5);
				},
				iconCls: 'pers-disp16',
				tooltip: BTN_PERSDISP_TIP
			}],
			region: 'east',
			width: 26
		});
		Ext.apply(this, 
		{
			border: true,
			style: 'height: 180px;',
			layout: 'form',
			listeners:
			{
				resize: function (p,nW, nH, oW, oH)
				{
					p.doLayout();
				},
				maximize: function (p,nW, nH, oW, oH)
				{
					p.doLayout();
				}
			},
			items: 
			{
				height: 160,
				frame: true,
				border: false,
				autoScroll: true,
				region: 'center',
				layout: 'border',
				items:
				[
					this.DataView,
					this.ButtonPanel
				]
			}
		});
		sw.Promed.PersonInfoPanel.superclass.initComponent.apply(this, arguments);
	}
});

/* 2009-07-09 */
sw.Promed.PersonDoublesInformationPanel = Ext.extend(Ext.Panel,
{
	border: false,
	button1Callback: Ext.emptyFn,
	button2Callback: Ext.emptyFn,
	button3Callback: Ext.emptyFn,
	button4Callback: Ext.emptyFn,
	button5Callback: Ext.emptyFn,
	button1OnHide: Ext.emptyFn,
	button2OnHide: Ext.emptyFn,
	button3OnHide: Ext.emptyFn,
	button4OnHide: Ext.emptyFn,
	button5OnHide: Ext.emptyFn,
	getFieldValue: function(field) {
		var result = '';
		if (this.items.items[0].getStore().getAt(0))
			result = this.items.items[0].getStore().getAt(0).get(field);
		return result;
	},
	height: 106,
	layout: 'border',
	load: function(params) {
		var callback_param = function() {}
		if ( params.callback )
		{
			callback_param = params.callback;
		}
		this.personId = params.Person_id;
		this.serverId = params.Server_id;
		this.items.items[0].getStore().removeAll();
		this.items.items[0].getStore().load({
			params: params,
			callback: callback_param
		});
	},
	panelButtonClick: function(winType) {
		var params = new Object();
		var window_name = '';

		switch (winType)
		{
			case 1:
				params.callback = this.button1Callback;
				params.onHide = this.button1OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				window_name = 'swPersonCardHistoryWindow';
				break;

			case 2:
				params.action = 'edit';
				params.callback = this.button2Callback;
				params.onClose = this.button2OnHide;
				window_name = 'swPersonEditWindow';
				break;

			case 3:
				params.callback = this.button3Callback;
				params.onHide = this.button3OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				window_name = 'swPersonCureHistoryWindow';
				break;

			case 4:
				params.callback = this.button4Callback;
				params.onHide = this.button4OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				window_name = 'swPersonPrivilegeViewWindow';
				break;

			case 5:
				params.callback = this.button5Callback;
				params.onHide = this.button5OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				window_name = 'swPersonDispHistoryWindow';
				break;

			default:
				return false;
				break;
		}

		params.Person_id = this.personId;
		params.Server_id = this.serverId;

		if (getWnd(window_name).isVisible())
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: 'Окно уже открыто',
				title: ERR_WND_TIT
			});
			return false;
		}

		getWnd(window_name).show(params);
	},
	personId: null,
	reset: function() {
		this.items.items[0].getStore().removeAll();
	},
	serverId: null,
	setParams: function(params) {
		if ( typeof params != 'object' ) {
			return false;
		}

		this.personId = params.Person_id;
		this.serverId = params.Server_id;
	},
	initComponent: function() {
		Ext.apply(this, {
			items: [ new Ext.DataView({
				border: false,
				frame: false,
				itemSelector: 'div',
				region: 'center',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					baseParams: {
						mode: 'PersonDoublesInformationPanel'
					},
					fields: [
						{name: 'Person_id'},
						{name: 'Server_pid'},
						{name: 'Document_begDate', dateFormat: 'd.m.Y', type: 'date'},
						{name: 'Document_Num'},
						{name: 'Document_Ser'},
						{name: 'KLAreaType_id'},
						{name: 'Lpu_Nick'},
						{name: 'Lpu_id'},
						{name: 'LpuRegion_Name'},
						{name: 'OrgDep_Name'},
						{name: 'OrgSmo_Name'},
						{name: 'Person_Age'},
						{name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date'},
						{name: 'PersonCard_begDate', dateFormat: 'd.m.Y', type: 'date'},
						{name: 'PersonEvn_id'},
						{name: 'Person_Firname'},
						{name: 'Person_Job'},
						{name: 'Person_PAddress'},
						{name: 'JobOrg_id'},
						{name: 'Person_Post'},
						{name: 'Person_RAddress'},
						{name: 'Person_Secname'},
						{name: 'Person_Snils'},
						{name: 'Person_Surname'},
						{name: 'Polis_begDate', dateFormat: 'd.m.Y', type: 'date'},
						{name: 'Polis_endDate', dateFormat: 'd.m.Y', type: 'date'},
						{name: 'Polis_Num'},
						{name: 'Polis_Ser'},
						{name: 'Sex_Code'},
						{name: 'Sex_id'},
						{name: 'SocStatus_id'},
						{name: 'Sex_Name'},
						{name: 'SocStatus_Name'},
						{name: 'Person_deadDT', dateFormat: 'd.m.Y', type: 'date'},
						{name: 'Person_closeDT', dateFormat: 'd.m.Y', type: 'date'},
						{name: 'PersonCloseCause_id'},
						{name: 'Person_IsDead'},
						{name: 'Person_IsBDZ'},
						{name: 'BDZ_Guid'},
						{name: 'Person_IsRefuse'},
						{name: 'PersonChild_id'}
					],
					url: '/?c=Common&m=loadPersonData'
				}),
				tpl: new Ext.XTemplate(
					'<tpl for=".">',
					'<div><b>Идентификатор сервера:</b> <font style="color: blue; font-weight: bold;">{Server_pid}</font><b>	Идентификатор ТФОМС:</b> <font style="color: blue; font-weight: bold;">{BDZ_Guid}</font></div>',
					'<div>ФИО: <font style="color: blue; font-weight: bold;">{Person_Surname} {Person_Firname} {Person_Secname}</font> Д/р: <font style="color: blue;">{[Ext.util.Format.date(values.Person_Birthday, "d.m.Y")]}</font> Пол: <font style="color: blue;">{Sex_Name}</font></div>',
					'<div>Соц. статус: <font style="color: blue;">{SocStatus_Name}</font> СНИЛС: <font style="color: blue;">{[snilsRenderer(values.Person_Snils)]}</font></div>',
					'<div>Регистрация: <font style="color: blue;">{Person_RAddress}</font></div>',
					'<div>Проживает: <font style="color: blue;">{Person_PAddress}</font></div>',
					'<div>Полис: <font style="color: blue;">{Polis_Ser} {Polis_Num}</font> Выдан: <font style="color: blue;">{[Ext.util.Format.date(values.Polis_begDate, "d.m.Y")]}, {OrgSmo_Name}</font>. Закрыт: <font style="color: blue;">{[Ext.util.Format.date(values.Polis_endDate, "d.m.Y")]}</font></div>',
					'<div>Документ: <font style="color: blue;">{Document_Ser} {Document_Num}</font> Выдан: <font style="color: blue;">{[Ext.util.Format.date(values.Document_begDate, "d.m.Y")]}, {OrgDep_Name}</font></div>',
					'<div>Работа: <font style="color: blue;">{Person_Job}</font> Должность: <font style="color: blue;">{Person_Post}</font></div>',
					'<div>МО: <font style="color: blue;">{Lpu_Nick}</font> Участок: <font style="color: blue;">{LpuRegion_Name}</font> Дата прикрепления: <font style="color: blue;">{[Ext.util.Format.date(values.PersonCard_begDate, "d.m.Y")]}</font></div>',
					'<div>Отказник: <font style="color: blue;">{[(values.Person_IsRefuse == "true")?"Да":"Нет"]}</div>',
					'</tpl>'
				)
			}),
			new Ext.Panel({
				border: false,
				defaults: {
					//minWidth: 130,
					xtype: 'button'
				},
				items: [{
					disabled: false,
					handler: function() {
						this.ownerCt.ownerCt.panelButtonClick(1);
					},
					text: BTN_PERSCARD,
					iconCls: 'pers-card16',
					tooltip: BTN_PERSCARD_TIP
				}, {
					disabled: false,
					handler: function() {
						this.ownerCt.ownerCt.panelButtonClick(2);
					},
					text: BTN_PERSEDIT,
					iconCls: 'edit16',
					tooltip: BTN_PERSEDIT_TIP
				}, {
					disabled: false,
					handler: function() {
						this.ownerCt.ownerCt.panelButtonClick(3);
					},
					text: BTN_PERSCUREHIST,
					iconCls: 'pers-curehist16',
					tooltip: BTN_PERSCUREHIST_TIP
				}, {
					disabled: false,
					handler: function() {
						this.ownerCt.ownerCt.panelButtonClick(4);
					},
					text: BTN_PERSPRIV,
					iconCls: 'pers-priv16',
					tooltip: BTN_PERSPRIV_TIP
				}, {
					disabled: false,
					handler: function() {
						this.ownerCt.ownerCt.panelButtonClick(5);
					},
					text: BTN_PERSDISP,
					iconCls: 'pers-disp16',
					tooltip: BTN_PERSDISP_TIP
				}],
				region: 'east',
				width: 124
			})]
		});
		sw.Promed.PersonDoublesInformationPanel.superclass.initComponent.apply(this, arguments);
	}
});
sw.Promed.EvnPSLocatInformationPanel = Ext.extend(Ext.Panel,
{
	border: false,
	getFieldValue: function(field) {
		var result = '';
		if (this.items.items[0].getStore().getAt(0))
			result = this.items.items[0].getStore().getAt(0).get(field);
		return result;
	},
	height: 35,
	layout: 'border',
	load: function(params) {
		this.items.items[0].getStore().removeAll();
		if (params.EvnPS_id && params.EvnPS_id != '') {
			var callback_param = (params.callback) ? params.callback : Ext.emptyFn;
			
			this.items.items[0].getStore().load({
				params: params,
				callback: callback_param
			});			
		} else {
			this.items.items[0].getStore().loadData([ params ]);
		}
	},
	initComponent: function() {
		var me = this;
		Ext.apply(this, {
			items: [ new Ext.DataView({
				border: false,
				frame: false,
				itemSelector: 'div',
				region: 'center',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					baseParams: {
						mode: 'EvnPSLocatInformationPanel'
					},
					fields: [
						{name: 'Person_id'},
						{name: 'Server_id'},
						{name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date'},
						{name: 'Person_Firname'},
						{name: 'Person_Secname'},
						{name: 'Person_Surname'},
						{name: 'Polis_Ser'},
						{name: 'Polis_Num'},
						{name: 'MedFIO'},
						{name: 'Post_Name'},
						{name: 'AmbulatCardLocatType_Name'}
						
					],
					url: '/?c=EvnPSLocat&m=loadMedicalHistory'
				}),
				style: 'padding: 1em;',
				tpl: new Ext.XTemplate(
					'<tpl for=".">',
					
					'<div><b>Пациент</b> <br>' +
						' ФИО:<font style="color: blue; font-weight: bold;">{Person_Surname} {Person_Firname} {Person_Secname}</font><br>' +
						' Д/р: <font style="color: blue;">{[Ext.util.Format.date(values.Person_Birthday, "d.m.Y")]}</font> г.р.<br>' +
						' Полис: Серия: <font style="color: blue;">{Polis_Ser}</font> Номер: <font style="color: blue;">{Polis_Num}</font> <br><br>' +
					'<br><b>Местонахождение</b> <br>' +
						' Врач: <font style="color: blue; font-weight: bold;">{MedFIO}</font> Должность: <font style="color: blue;">{Post_Name}</font> <br>' +
						' Местонахождение: <font style="color: blue;">{AmbulatCardLocatType_Name}</font> </div>' +
					'</tpl>'
				)
			})]
		});

		sw.Promed.EvnPSLocatInformationPanel.superclass.initComponent.apply(this, arguments);
	}
});

sw.Promed.PersonInformationPanelShort = Ext.extend(Ext.Panel,
{
	border: false,
	getFieldValue: function(field) {
		var result = '';
		if (this.items.items[0].getStore().getAt(0))
			result = this.items.items[0].getStore().getAt(0).get(field);
		return result;
	},
	height: 35,
	layout: 'border',
	load: function(params) {
		var ths = this;
		this.items.items[0].getStore().removeAll();
		//alert('Load data for #' + params.Person_id);
		if (params.Person_id && params.Person_id != '') {
			var callback_param = (params.callback) ? params.callback : Ext.emptyFn;
			
			this.items.items[0].getStore().load({
				params: params,
				callback: callback_param
			});			
		} else {
			this.items.items[0].getStore().loadData([ params ]);
		}
		
		if (params.userMedStaffFact) {
			ths.userMedStaffFact = params.userMedStaffFact;
		}

		if (params.EvnDirection_pid) {//направление имеет связанное направление (родительское) из арм диагностики
			Ext.Ajax.request({
				url: '?c=EvnUslugaTelemed&m=loadParentEvnDirection',
				success: function(resp){
					var data = Ext.util.JSON.decode( resp.responseText );
					if(!Ext.isEmpty(data)) {
						ths.pEvnDirectionData = data;
						if(data && data.EvnClass_SysNick=='EvnUslugaPar')
							ths.EvnDirection_pid = params.EvnDirection_pid;
						
						ths.dataview.refresh();
					}
				},
				failure: function(){
				},
				params: {
					EvnDirection_id: params.EvnDirection_pid
				}
			});
		}
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible()) //https://redmine.swan.perm.ru/issues/92555 Эта панель дохрена где используется, а для АРМа МЗ ее надо скрыть, поэтому чем перелопачивать туеву хучу форм, сделал такой вот финт ушами
			this.disable();
		else
			this.enable();
	},
	initComponent: function() {
/*
		var parent = this.findParentBy(function(cmp, panel) {
			return true;
		});

		parent.addListener('keydown', function(inp, e) {
			if (e.getKey() == Ext.EventObject.F7)
			{
				e.stopEvent();
				console.info('F7');
			}
		});
*/
		var me = this;
		me.openParEvnDir = function() {
			getWnd('swEvnUslugaFuncRequestDicomViewerEditWindow').show({
				EvnUslugaPar_id: this.pEvnDirectionData.EvnUslugaPar_id
				,action: 'view'
				,Lpu_id: this.pEvnDirectionData.Lpu_id
				,Person_id: this.getFieldValue('Person_id')
				,userMedStaffFact: this.userMedStaffFact
				,MedService_id: this.pEvnDirectionData ? this.pEvnDirectionData.MedService_id : null
			});
		};
		Ext.apply(this, {
			items: [ me.dataview = new Ext.DataView({
				border: false,
				frame: false,
				itemSelector: 'div',
				region: 'center',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					baseParams: {
						mode: 'PersonInformationPanelShort'
					},
					fields: [
						{name: 'Person_id'},
						{name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date'},
						{name: 'Person_Firname'},
						{name: 'Person_Secname'},
						{name: 'Person_Surname'},
						{name: 'Person_deadDT', dateFormat: 'd.m.Y', type: 'date'},
						{name: 'Person_closeDT', dateFormat: 'd.m.Y', type: 'date'},
						{name: 'Person_Snils'},
						{name: 'Sex_Code'},
						{name: 'Sex_Name'},
						{name: 'OmsSprTerr_id'},
						{name: 'OmsSprTerr_Code'},
						{name: 'PersonEncrypHIV_Encryp'},
						{name: 'Server_id'},
						{name: 'PersonEvn_id'},
						{name: 'Person_IsAnonym'},
						{name: 'JobOrg_id'},
						{name: 'DocumentType_id'},
						{name: 'PersonChild_id'}
					],
					url: '/?c=Common&m=loadPersonData'
				}),
				style: 'padding: 1em;',
				tpl: new Ext6.XTemplate(
					'<tpl for=".">',
					'<tpl if="isMseDepers()">',
					'<div>Пациент: ' +
						'<font style="color: blue; font-weight: bold;">*** *** ***</font>' +
						' Д/р: <font style="color: blue;">***</font> г.р. ' +
						'Пол: <font style="color: blue;">{Sex_Name}</font></div>',
						'<tpl if="this.hasParentEvnDirection()">',
							' &nbsp;Данные для повторного анализа: <a id="PersInfPanS_evnusluga" href=\'javascript:void(0)\' onClick="Ext.getCmp(\''+this.id+'\').openParEvnDir();">{[this.getUslugaName()]}</a>',
							'<tpl if="this.checkAttachedImage()">',
								' &nbsp<a id="PersInfPanS_evnusluga_digi" href="{[this.getLinkImage()]}" target="_blank">Ссылка в DigiPacs</a>',
							'</tpl>',
						'</tpl>',
					'<tpl elseif="this.allowPersonEncrypHIV()">',
					'<div>Шифр: <font style="color: blue;">{PersonEncrypHIV_Encryp}</font></div>',
					'<tpl else>',
					'<div>Пациент: ' +
						'<font style="color: blue; font-weight: bold;">{Person_Surname} {Person_Firname} {Person_Secname}</font>' +
						' Д/р: <font style="color: blue;">{[Ext.util.Format.date(values.Person_Birthday, "d.m.Y")]}</font> г.р. {[(String(values.Person_deadDT) != "" ? "&nbsp;' +
						'Дата смерти: <font color=red>" + String(Ext.util.Format.date(values.Person_deadDT, "d.m.Y")) + "</font>" : "&nbsp;")]} {[(String(values.Person_closeDT) != "" ? "&nbsp;' +
						'Дата закрытия: <font color=red>" + String(Ext.util.Format.date(values.Person_closeDT, "d.m.Y")) + "</font>" : "&nbsp;")]}' +
						'Пол: <font style="color: blue;">{Sex_Name}</font>',
						'<tpl if="this.hasParentEvnDirection()">',
							' &nbsp;Данные для повторного анализа: <a id="PersInfPanS_evnusluga" href=\'javascript:void(0)\' onClick="Ext.getCmp(\''+this.id+'\').openParEvnDir();">{[this.getUslugaName()]}</a>',
							'<tpl if="this.checkAttachedImage()">',
								' &nbsp<a id="PersInfPanS_evnusluga_digi" href="{[this.getLinkImage()]}" target="_blank">Ссылка в DigiPacs</a>',
							'</tpl>',
						'</tpl>',
						'</div>',
					'</tpl>',
					'</tpl>',
					{
						allowPersonEncrypHIV: function() {
							return (!Ext.isEmpty(me.getFieldValue('PersonEncrypHIV_Encryp')));
						},
						hasParentEvnDirection: function() {
							return !Ext.isEmpty(me.EvnDirection_pid);
						},
						getUslugaName: function() {
							return me.pEvnDirectionData.UslugaComplex_Name;
						},
						checkAttachedImage: function() {
							return !Ext.isEmpty(me.pEvnDirectionData.Study_uid);
						},
						getLinkImage: function() {
							return 'http://'+ me.pEvnDirectionData.PACS_ip_vip +'/#/viewer/token-auth?token=user&n=%2Fviewer%2Fredirect-to-image-view%3FStudy%3D' + me.pEvnDirectionData.Study_uid + '%26serverName%3DPACS';
						}
					}
				)
			})]
		});

		sw.Promed.PersonInformationPanelShort.superclass.initComponent.apply(this, arguments);
	}
});

sw.Promed.PersonInformationPanelShortWithDirection = Ext.extend(Ext.Panel,{
	border: false,
	showHistoryLink: true,
	getFieldValue: function(field) {
		var result = '';
		if (this.patientDataView.getStore().getAt(0))
			result = this.patientDataView.getStore().getAt(0).get(field);
		return result;
	},
	height: 35,
	layout: 'border',
	showINN: false,
	load: function(params) {
		this.patientDataView.getStore().removeAll();
		//alert('Load data for #' + params.Person_id);
		if (params.Person_id && params.Person_id != '') {
			var callback_param = (params.callback) ? params.callback : Ext.emptyFn;
			this.patientDataView.getStore().load({
				params: params,
				callback: callback_param
			});			
		} else {
			this.patientDataView.getStore().loadData([ params ]);
		}
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible()) //https://redmine.swan.perm.ru/issues/92555 Эта панель дохрена где используется, а для АРМа МЗ ее надо скрыть, поэтому чем перелопачивать туеву хучу форм, сделал такой вот финт ушами
			this.disable();
		else
			this.enable();
	},
	initComponent: function() {
		this.openPersonHistoryWindow = function() {
			getWnd('swEvnUslugaFuncRequestPatientHistoryWindow').show({
				Person_id: this.patientDataView.getStore().getAt(0).get('Person_id')
			});
		}.createDelegate(this);
		
		this.openEmk = function() {
			getWnd('swPersonEmkWindow').show({
				Person_id: this.patientDataView.getStore().getAt(0).get('Person_id'),
				Server_id: this.patientDataView.getStore().getAt(0).get('Server_id'),
				PersonEvn_id: this.patientDataView.getStore().getAt(0).get('PersonEvn_id'),
				ARMType: 'common',
				readOnly: true
			});
		}.createDelegate(this);
		
		var historyLink = '',
			Person_inn = '';
		if (this.showHistoryLink) {
			historyLink = '{[( "&nbsp;&nbsp;&nbsp; <a  onclick=\'"+values.history+"\'\' href=\'javascript:void(0)\'>Архив изображений</a>")]}';
		}
		if (getRegionNick() == "kz" && this.showINN) {
			Person_inn = '&nbsp;ИИН: <font style="color: blue; font-weight: bold;">{Person_Inn}</font>';
		}
		var panelTemplate = new Ext.XTemplate(
			'<tpl for=".">',
			'<div>Пациент: ' +
				'<font style="color: blue; font-weight: bold;">{Person_Surname} {Person_Firname} {Person_Secname}</font>' +
				Person_inn +
				' Д/р: <font style="color: blue;">{[Ext.util.Format.date(values.Person_Birthday, "d.m.Y")]}</font> г.р. \n\
				{[(String(values.Person_deadDT) != "" ? "&nbsp; Дата смерти: <font color=red>" + String(Ext.util.Format.date(values.Person_deadDT, "d.m.Y")) + "</font>" : "&nbsp;")]} {[(String(values.Person_closeDT) != "" ? "&nbsp;' +
				'Дата закрытия: <font color=red>" + String(Ext.util.Format.date(values.Person_closeDT, "d.m.Y")) + "</font>" : "&nbsp;")]}'+
				'{[( ((String(values.EvnDirection_Num) != "")&&(String(values.EvnDirection_id) != "")) ? "&nbsp; Направление: <a onclick=\''+
						'"+values.link+"\''+
						'\' href=\'javascript:void(0)\'><font color=blue>" +"№ " + values.EvnDirection_Num + '+
						'(String(values.EvnDirection_setDT) != "" ? "&nbsp; от " + values.EvnDirection_setDT + " &nbsp; г." : "&nbsp;")+'+
				'"</font></a>" : "&nbsp;")]}'+	
				historyLink +
				'{[( "&nbsp;&nbsp;&nbsp; <a  onclick=\''+
				'"+values.openemk+"\''+
				'\' href=\'javascript:void(0)\'>Просмотреть ЭМК</a>")]}'+
				'</div>',
			'</tpl>'
		);
			
		this.patientDataView = new Ext.DataView({
			border: false,
			frame: false,
			itemSelector: 'div',
			region: 'center',
			store: new Ext.data.JsonStore({
				autoLoad: false,
				baseParams: {
					mode: 'PersonInformationPanelShortWithDirection'
				},
				listeners: {
					'load': function(store, records) {
						records[0].set('link','Ext.getCmp("EvnUslugaFuncRequestDicomViewerEditWindow").openEvnFuncRequestEditWindowViewMode()');
						records[0].set('history','Ext.getCmp("'+this.id+'").openPersonHistoryWindow()');
						records[0].set('openemk','Ext.getCmp("'+this.id+'").openEmk()');
					}.createDelegate(this)
				},
				fields: [
					{name: 'Person_id'},
					{name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date'},
					{name: 'Person_Firname'},
					{name: 'Person_Secname'},
					{name: 'Person_Surname'},
					{name: 'Person_deadDT', dateFormat: 'd.m.Y', type: 'date'},
					{name: 'Person_closeDT', dateFormat: 'd.m.Y', type: 'date'},
					{name: 'Sex_Code'},
					{name: 'OmsSprTerr_id'},
					{name: 'OmsSprTerr_Code'},
					{name: 'Server_id'},
					{name: 'PersonEvn_id'},
					{name: 'Person_Inn'},
					{name: 'EvnDirection_Num'},
					{name: 'EvnDirection_setDT'},
					{name: 'EvnDirection_id'},
					{name: 'link'},
					{name: 'history'},
					{name: 'openemk'},
					{name: 'PersonChild_id'}
				],
				url: '/?c=Common&m=loadPersonData'
			}),
			style: 'padding: 1em; ',
			tpl: panelTemplate
		});
		
		Ext.apply(this, {
			items: [ 
				this.patientDataView
			]
		});
		sw.Promed.PersonInformationPanelShortWithDirection.superclass.initComponent.apply(this, arguments);
	}
});

sw.Promed.SearchFilterPanel = Ext.extend(sw.Promed.FormPanel, {
	autoScroll: true,
	bodyBorder: false,
	border: false,
	buttonAlign: 'left',
	clearAddressCombo: function(level) {
		var form = this;

		var country_combo = form.getForm().findField('KLCountry_id');
		var region_combo = form.getForm().findField('KLRgn_id');
		var subregion_combo = form.getForm().findField('KLSubRgn_id');
		var city_combo = form.getForm().findField('KLCity_id');
		var town_combo = form.getForm().findField('KLTown_id');
		var street_combo = form.getForm().findField('KLStreet_id');

		var klarea_pid = 0;

		switch (level)
		{
			case 0:
				country_combo.clearValue();
				region_combo.clearValue();
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				region_combo.getStore().removeAll();
				subregion_combo.getStore().removeAll();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				street_combo.getStore().removeAll();
				break;

			case 1:
				region_combo.clearValue();
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				subregion_combo.getStore().removeAll();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				street_combo.getStore().removeAll();
				break;

			case 2:
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				street_combo.getStore().removeAll();

				if (region_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}

				form.loadAddressCombo(level, 0, klarea_pid, true);
				break;

			case 3:
				city_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				town_combo.getStore().removeAll();
				street_combo.getStore().removeAll();

				if (subregion_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}
				else if (region_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}

				form.loadAddressCombo(level, 0, klarea_pid, true);
				break;

			case 4:
				town_combo.clearValue();
				street_combo.clearValue();

				street_combo.getStore().removeAll();

				if (city_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}
				else if (subregion_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}
				else if (region_combo.getValue() != null)
				{
					klarea_pid = region_combo.getValue();
				}

				form.loadAddressCombo(level, 0, klarea_pid, true);
				break;
		}
	},
	doReset: Ext.emptyFn,
	doSearch: Ext.emptyFn,
	frame: false,
	height: 270,
	initComponent: function() {
		Ext.apply(this, {
			items: [ new Ext.TabPanel({
				activeTab: 0,
				defaults: {bodyStyle: 'padding: 0px'},
				height: 270,
				id: this.tabPanelId,
				layoutOnTabChange: true,
				listeners: {
					'tabchange': function(panel, tab) {
						//
					}
				},
				plain: true,

				items: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					title: langs('1. Основной поиск'),

					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: langs('Фамилия'),
								name: 'Person_Surname',
								width: 200,
								xtype: 'textfieldpmw'
							}, {
								fieldLabel: langs('Имя'),
								name: 'Person_Firname',
								width: 200,
								xtype: 'textfieldpmw'
							}, {
								fieldLabel: langs('Отчество'),
								name: 'Person_Secname',
								width: 200,
								xtype: 'textfieldpmw'
							}]
						}, {
							border: false,
							labelWidth: 160,
							layout: 'form',
							items: [{
								fieldLabel: langs('Дата рождения'),
								name: 'Person_Birthday',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999', false)
								],
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: langs('Диапазон дат рождения'),
								name: 'Person_Birthday_Range',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								width: 170,
								xtype: 'daterangefield'
							}, {
								fieldLabel: langs('Номер амб. карты'),
								name: 'PersonCard_Code',
								width: 100,
								xtype: 'textfield'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowNegative: false,
								allowDecimals: false,
								fieldLabel: langs('Возраст'),
								name: 'PersonAge',
								width: 60,
								xtype: 'numberfield'
							}, {
								allowNegative: false,
								allowDecimals: false,
								fieldLabel: langs('Год рождения'),
								name: 'PersonBirthdayYear',
								width: 60,
								xtype: 'numberfield'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								allowNegative: false,
								allowDecimals: false,
								fieldLabel: langs('Возраст с'),
								name: 'PersonAge_Min',
								width: 61,
								xtype: 'numberfield'
							}, {
								allowNegative: false,
								allowDecimals: false,
								fieldLabel: langs('Год рождения с'),
								name: 'PersonBirthdayYear_Min',
								width: 61,
								xtype: 'numberfield'
							}]
						}, {
							border: false,
							labelWidth: 40,
							layout: 'form',
							items: [{
								allowNegative: false,
								allowDecimals: false,
								fieldLabel: langs('по'),
								name: 'PersonAge_Max',
								width: 61,
								xtype: 'numberfield'
							}, {
								allowNegative: false,
								allowDecimals: false,
								fieldLabel: langs('по'),
								name: 'PersonBirthdayYear_Max',
								width: 61,
								xtype: 'numberfield'
							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: langs('Полис'),
						width: 755,
						xtype: 'fieldset',

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: langs('Серия'),
									name: 'Polis_Ser',
									width: 100,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								labelWidth: 100,
								layout: 'form',
								items: [{
									allowNegative: false,
									allowDecimals: false,
									fieldLabel: langs('Номер'),
									name: 'Polis_Num',
									width: 100,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								labelWidth: 130,
								layout: 'form',
								items: [{
									allowNegative: false,
									allowDecimals: false,
									fieldLabel: langs('Единый номер'),
									name: 'Person_Code',
									width: 162,
									xtype: 'numberfield'
								}]
							}]
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									width: 100,
									xtype: 'swpolistypecombo'
								}]
							}, {
								border: false,
								labelWidth: 100,
								layout: 'form',
								items: [{
									enableKeyEvents: true,
									forceSelection: false,
									hiddenName: 'OrgSmo_id',
									listeners: {
										'blur': function(combo) {
											if (combo.getRawValue() == '')
												combo.clearValue();

											if ( combo.getStore().find(combo.displayField, combo.getRawValue()) < 0 )
												combo.clearValue();
										},
										'keydown': function( inp, e ) {
											if ( e.F4 == e.getKey() )
											{
												if ( inp.disabled )
													return;

												if ( e.browserEvent.stopPropagation )
													e.browserEvent.stopPropagation();
												else
													e.browserEvent.cancelBubble = true;

												if ( e.browserEvent.preventDefault )
													e.browserEvent.preventDefault();
												else
													e.browserEvent.returnValue = false;

												e.returnValue = false;

												if ( Ext.isIE )
												{
													e.browserEvent.keyCode = 0;
													e.browserEvent.which = 0;
												}

												inp.onTrigger2Click();
												inp.collapse();

												return false;
											}
										},
										'keyup': function(inp, e) {
											if ( e.F4 == e.getKey() )
											{
												if ( e.browserEvent.stopPropagation )
													e.browserEvent.stopPropagation();
												else
													e.browserEvent.cancelBubble = true;

												if ( e.browserEvent.preventDefault )
													e.browserEvent.preventDefault();
												else
													e.browserEvent.returnValue = false;

												e.returnValue = false;

												if ( Ext.isIE )
												{
													e.browserEvent.keyCode = 0;
													e.browserEvent.which = 0;
												}

												return false;
											}
										}
									},
									listWidth: 400,
									minChars: 1,
									onTrigger2Click: function() {
										if ( this.disabled )
											return;

										var combo = this;

										getWnd('swOrgSearchWindow').show({
											object: 'smo',
											onClose: function() {
												combo.focus(true, 200);
											},
											onSelect: function(orgData) {
												if ( orgData.Org_id > 0 )
												{
													combo.setValue(orgData.Org_id);
													combo.focus(true, 250);
													combo.fireEvent('change', combo);
												}
												getWnd('swOrgSearchWindow').hide();
											}
										});
									},
									queryDelay: 1,
									typeAhead: true,
									typeAheadDelay: 1,
									width: 400,
									xtype: 'sworgsmocombo'
								}]
							}]
						}, {
							width: 310,
							xtype: 'swomssprterrcombo'
						}]
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					title: langs('<u>2</u>. Пациент'),

					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: langs('Пол'),
								hiddenName: 'Sex_id',
								width: 150,
								xtype: 'swpersonsexcombo'
							}, {
								fieldLabel: langs('СНИЛС'),
								name: 'Person_Snils',
								width: 150,
								xtype: 'textfieldpmw'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: langs('Соц. статус'),
								hiddenName: 'SocStatus_id',
								width: 250,
								xtype: 'swsocstatuscombo'
							}, {
								codeField: 'PrivilegeType_Code',
								displayField: 'PrivilegeType_Name',
								editable: false,
								fieldLabel: langs('Категория льготы'),
								hiddenName: 'PersonPrivilegeType_id',
								lastQuery: '',
								listWidth: 350,
								store: new Ext.db.AdapterStore({
									autoLoad: true,
									dbFile: 'Promed.db',
									fields: [
										{name: 'PrivilegeType_id', type: 'int'},
										{name: 'PrivilegeType_Code', type: 'int'},
										{name: 'PrivilegeType_Name', type: 'string'},
										{name: 'ReceptDiscount_id', type: 'int'},
										{name: 'ReceptFinance_id', type: 'int'}
									],
									key: 'PrivilegeType_id',
									sortInfo: {field: 'PrivilegeType_Code'},
									tableName: 'PrivilegeType'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<font color="red">{PrivilegeType_Code}</font>&nbsp;{PrivilegeType_Name}',
									'</div></tpl>'
								),
								valueField: 'PrivilegeType_id',
								width: 250,
								xtype: 'swbaselocalcombo'
							}]
						}]
					},
					new sw.Promed.SwYesNoCombo({
						disabled: true,
						fieldLabel: langs('Диспансерное наблюдение'),
						hiddenName: 'PersonDisp_id',
						width: 100
					}), {
						autoHeight: true,
						labelWidth: 114,
						layout: 'form',
						style: 'margin: 0px 5px 0px 5px; padding: 0px;',
						title: langs('Документ'),
						xtype: 'fieldset',

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									editable: false,
									fieldLabel: langs('Тип'),
									forceSelection: true,
									hiddenName: 'DocumentType_id',
									listWidth: 500,
									width: 200,
									xtype: 'swdocumenttypecombo'
								}]
							}, {
								border: false,
								labelWidth: 80,
								layout: 'form',
								items: [{
									fieldLabel: langs('Серия'),
									name: 'Document_Ser',
									width: 100,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								labelWidth: 80,
								layout: 'form',
								items: [{
									allowNegative: false,
									allowDecimals: false,
									fieldLabel: langs('Номер'),
									name: 'Document_Num',
									width: 100,
									xtype: 'numberfield'
								}]
							}]
						}, {
							editable: false,
							enableKeyEvents: true,
							hiddenName: 'OrgDep_id',
							listeners: {
								'keydown': function( inp, e ) {
									if ( inp.disabled )
										return;

									if ( e.F4 == e.getKey() )
									{
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.returnValue = false;

										if ( Ext.isIE )
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										inp.onTrigger1Click();

										return false;
									}
								},
								'keyup': function(inp, e) {
									if ( e.F4 == e.getKey() )
									{
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.returnValue = false;

										if ( Ext.isIE )
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										return false;
									}
								}
							},
							listWidth: 400,
							onTrigger1Click: function() {
								if ( this.disabled )
								{
									return;
								}

								var combo = this;

								getWnd('swOrgSearchWindow').show({
									onSelect: function(orgData) {
										if ( orgData.Org_id > 0 )
										{
											combo.getStore().load({
												params: {
													Object: 'OrgDep',
													OrgDep_id: orgData.Org_id,
													OrgDep_Name: ''
												},
												callback: function()
												{
													combo.setValue(orgData.Org_id);
													combo.focus(true, 250);
													combo.fireEvent('change', combo);
												}
											});
										}
										getWnd('swOrgSearchWindow').hide();
									},
									onClose: function() {
										combo.focus(true, 200)
									},
									object: 'dep'
								});
							},
							width: 500,
							xtype: 'sworgdepcombo'
						}]
					}, {
						autoHeight: true,
						labelWidth: 114,
						layout: 'form',
						style: 'margin: 0px 5px 5px 5px; padding: 0px;',
						title: langs('Место работы, учебы'),
						xtype: 'fieldset',

						items: [{
							editable: false,
							enableKeyEvents: true,
							fieldLabel: langs('Организация'),
							hiddenName: 'Org_id',
							listeners: {
								'keydown': function( inp, e ) {
									if ( e.F4 == e.getKey() )
									{
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.returnValue = false;

										if ( Ext.isIE )
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										inp.onTrigger1Click();
										return false;
									}
								},
								'keyup': function(inp, e) {
									if ( e.F4 == e.getKey() )
									{
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.returnValue = false;

										if ( Ext.isIE )
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										return false;
									}
								}
							},
							onTrigger1Click: function() {
								var combo = this;

								getWnd('swOrgSearchWindow').show({
									onSelect: function(orgData) {
										if ( orgData.Org_id > 0 )
										{
											combo.getStore().load({
												params: {
													Object: 'Org',
													Org_id: orgData.Org_id,
													Org_Name: ''
												},
												callback: function()
												{
													combo.setValue(orgData.Org_id);
													combo.focus(true, 500);
													combo.fireEvent('change', combo);
												}
											});
										}
										getWnd('swOrgSearchWindow').hide();
									},
									onClose: function() {combo.focus(true, 200)}
								});
							},
							triggerAction: 'none',
							width: 500,
							xtype: 'sworgcombo'
						}, {
							forceSelection: false,
							hiddenName: 'Post_id',
							minChars: 0,
							queryDelay: 1,
							selectOnFocus: true,
							typeAhead: true,
							typeAheadDelay: 1,
							width: 500,
							xtype: 'swpostcombo'
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [ new sw.Promed.SwYesNoCombo({
								fieldLabel: langs('Отказник'),
								hiddenName: 'Refuse_id',
								width: 100
							})]
						}, {
							border: false,
							layout: 'form',
							items: [ new sw.Promed.SwYesNoCombo({
								fieldLabel: langs('Отказ на след. год'),
								hiddenName: 'RefuseNextYear_id',
								width: 100
							})]
						}]
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					title: langs('3. Картотека'),

					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: langs('Дата прикрепления'),
								name: 'PersonCard_begDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: langs('Дата открепления'),
								name: 'PersonCard_endDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
								width: 100,
								xtype: 'swdatefield'
							}, {
								border: false,
								layout: 'form',
								items: [{
									hiddenName: 'LpuRegionType_id',
									width: 170,
									xtype: 'swlpuregiontypecombo'
								}]
							}]
						}, {
							border: false,
							labelWidth: 220,
							layout: 'form',
							items: [{
								fieldLabel: langs('Диапазон дат прикрепления'),
								name: 'PersonCard_begDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								width: 170,
								xtype: 'daterangefield'
							}, {
								fieldLabel: langs('Диапазон дат открепления'),
								name: 'PersonCard_endDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								width: 170,
								xtype: 'daterangefield'
							}, {
								displayField: 'LpuRegion_Name',
								editable: true,
								fieldLabel: langs('Участок'),
								forceSelection: false,
								hiddenName: 'LpuRegion_id',
								lastQuery: '',
								listeners: {
									'blur': function(combo) {
										if ( combo.getRawValue() == '' )
											combo.clearValue();
										if ( combo.getStore().find(combo.displayField, combo.getRawValue()) < 0 )
											combo.clearValue();
									}
								},
								mode: 'local',
								queryDelay: 1,
								store: new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader({
										id: 'LpuRegion_id'
									}, [
										{name: 'LpuRegion_id', mapping: 'LpuRegion_id'},
										{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
										{name: 'LpuRegionType_id', mapping: 'LpuRegionType_id'},
										{name: 'LpuRegion_Name', mapping: 'LpuRegion_Name'}
									]),
									sortInfo: {
										field: 'LpuRegion_Name'
									},
									url: C_LPUREGION_LIST
								}),
								triggerAction: 'all',
								typeAhead: true,
								typeAheadDelay: 1,
								valueField: 'LpuRegion_id',
								width: 250,
								xtype: 'combo'
							}]
						}]
					}, {
						codeField: 'MedPersonal_Code',
						disabled: true,
						displayField: 'MedPersonal_Fio',
						enableKeyEvents: true,
						editable: false,
						fieldLabel: langs('Врач'),
						hiddenName: 'MedPersonal_id',
						listWidth: 350,
						mode: 'local',
						resizable: true,
						store: new Ext.data.Store({
							autoLoad: false,
							reader: new Ext.data.JsonReader({
								id: 'MedPersonal_id'
							}, [
								{name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio'},
								{name: 'MedPersonal_id', mapping: 'MedPersonal_id'},
								{name: 'MedPersonal_Code', mapping: 'MedPersonal_Code'}
							]),
							sortInfo: {
								field: 'MedPersonal_Fio'
							},
							url: C_MP_LOADLIST
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<table style="border: 0;"><td style="width: 40px"><font color="red">{MedPersonal_Code}</font></td><td><h3>{MedPersonal_Fio}</h3></td></tr></table>',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'MedPersonal_id',
						width : 310,
						xtype: 'swbaselocalcombo'
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					title: langs('4. Адрес'),

					items: [{
						codeField: 'KLAreaStat_Code',
						disabled: false,
						displayField: 'KLArea_Name',
						editable: true,
						enableKeyEvents: true,
						fieldLabel: langs('Территория'),
						hiddenName: 'KLAreaStat_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var form = combo.ownerCt.ownerCt.ownerCt;
								var record = combo.getStore().getById(newValue);

								var country_combo = form.getForm().findField('KLCountry_id');
								var rgn_combo = form.getForm().findField('KLRgn_id');
								var subrgn_combo = form.getForm().findField('KLSubRgn_id');
								var city_combo = form.getForm().findField('KLCity_id');
								var town_combo = form.getForm().findField('KLTown_id');
								var street_combo = form.getForm().findField('KLStreet_id');

								country_combo.enable();
								rgn_combo.enable();
								subrgn_combo.enable();
								city_combo.enable();
								town_combo.enable();
								street_combo.enable();

								if ( !record )
								{
									return false;
								}

								var country_id = record.get('KLCountry_id');
								var region_id = record.get('KLRGN_id');
								var subregion_id = record.get('KLSubRGN_id');
								var city_id = record.get('KLCity_id');
								var town_id = record.get('KLTown_id');
								var klarea_pid = 0;
								var level = 0;

								form.clearAddressCombo(country_combo.areaLevel);

								if (country_id != null)
								{
									country_combo.setValue(country_id);
									country_combo.disable();
								}
								else
								{
									return false;
								}

								rgn_combo.getStore().load({
									callback: function() {
										rgn_combo.setValue(region_id);
									},
									params: {
										country_id: country_id,
										level: 1,
										value: 0
									}
								});

								if (region_id.toString().length > 0)
								{
									klarea_pid = region_id;
									level = 1;
								}

								subrgn_combo.getStore().load({
									callback: function() {
										subrgn_combo.setValue(subregion_id);
									},
									params: {
										country_id: 0,
										level: 2,
										value: klarea_pid
									}
								});

								if (subregion_id.toString().length > 0)
								{
									klarea_pid = subregion_id;
									level = 2;
								}

								city_combo.getStore().load({
									callback: function() {
										city_combo.setValue(city_id);
									},
									params: {
										country_id: 0,
										level: 3,
										value: klarea_pid
									}
								});

								if (city_id.toString().length > 0)
								{
									klarea_pid = city_id;
									level = 3;
								}

								town_combo.getStore().load({
									callback: function() {
										town_combo.setValue(town_id);
									},
									params: {
										country_id: 0,
										level: 4,
										value: klarea_pid
									}
								});

								if (town_id.toString().length > 0)
								{
									klarea_pid = town_id;
									level = 4;
								}

								street_combo.getStore().load({
									params: {
										country_id: 0,
										level: 5,
										value: klarea_pid
									}
								});

								switch (level)
								{
									case 1:
										rgn_combo.disable();
										break;

									case 2:
										rgn_combo.disable();
										subrgn_combo.disable();
										break;

									case 3:
										rgn_combo.disable();
										subrgn_combo.disable();
										city_combo.disable();
										break;

									case 4:
										rgn_combo.disable();
										subrgn_combo.disable();
										city_combo.disable();
										town_combo.disable();
										break;
								}
							}
						},
						store: new Ext.db.AdapterStore({
							autoLoad: true,
							dbFile: 'Promed.db',
							fields: [
								{name: 'KLAreaStat_id', type: 'int'},
								{name: 'KLAreaStat_Code', type: 'int'},
								{name: 'KLArea_Name', type: 'string'},
								{name: 'KLCountry_id', type: 'int'},
								{name: 'KLRGN_id', type: 'int'},
								{name: 'KLSubRGN_id', type: 'int'},
								{name: 'KLCity_id', type: 'int'},
								{name: 'KLTown_id', type: 'int'}
							],
							key: 'KLAreaStat_id',
							sortInfo: {
								field: 'KLAreaStat_Code',
								direction: 'ASC'
							},
							tableName: 'KLAreaStat'
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}',
							'</div></tpl>'
						),
						valueField: 'KLAreaStat_id',
						width: 300,
						xtype: 'swbaselocalcombo'
					}, {
						areaLevel: 0,
						codeField: 'KLCountry_Code',
						disabled: false,
						displayField: 'KLCountry_Name',
						editable: true,
						fieldLabel: langs('Страна'),
						hiddenName: 'KLCountry_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var form = combo.ownerCt.ownerCt.ownerCt;

								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									form.loadAddressCombo(combo.areaLevel, combo.getValue(), 0, true);
								}
								else
								{
									form.clearAddressCombo(combo.areaLevel);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE)
								{
									if (combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
									{
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								}
							},
							'select': function(combo, record, index) {
								if (record.get('KLCountry_id') == combo.getValue())
								{
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.get('KLArea_id'), null);
							}
						},
						store: new Ext.db.AdapterStore({
							autoLoad: true,
							dbFile: 'Promed.db',
							fields: [
								{name: 'KLCountry_id', type: 'int'},
								{name: 'KLCountry_Code', type: 'int'},
								{name: 'KLCountry_Name', type: 'string'}
							],
							key: 'KLCountry_id',
							sortInfo: {
								field: 'KLCountry_Name'
							},
							tableName: 'KLCountry'
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}',
							'</div></tpl>'
						),
						valueField: 'KLCountry_id',
						width: 300,
						xtype: 'swbaselocalcombo'
					}, {
						areaLevel: 1,
						disabled: false,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Регион'),
						hiddenName: 'KLRgn_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var form = combo.ownerCt.ownerCt.ownerCt;

								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									form.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
								}
								else
								{
									form.clearAddressCombo(combo.areaLevel);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if (record.get('KLArea_id') == combo.getValue())
								{
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.get('KLArea_id'));
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'KLArea_id', type: 'int'},
								{name: 'KLArea_Name', type: 'string'}
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{KLArea_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLArea_id',
						width: 300,
						xtype: 'combo'
					}, {
						areaLevel: 2,
						disabled: false,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Район'),
						hiddenName: 'KLSubRgn_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var form = combo.ownerCt.ownerCt.ownerCt;

								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									form.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
								}
								else
								{
									form.clearAddressCombo(combo.areaLevel);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if (record.get('KLArea_id') == combo.getValue())
								{
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.get('KLArea_id'));
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'KLArea_id', type: 'int'},
								{name: 'KLArea_Name', type: 'string'}
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{KLArea_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLArea_id',
						width: 300,
						xtype: 'combo'
					}, {
						areaLevel: 3,
						disabled: false,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Город'),
						hiddenName: 'KLCity_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var form = combo.ownerCt.ownerCt.ownerCt;

								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									form.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if (record.get('KLArea_id') == combo.getValue())
								{
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.get('KLArea_id'));
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'KLArea_id', type: 'int'},
								{name: 'KLArea_Name', type: 'string'}
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{KLArea_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLArea_id',
						width: 300,
						xtype: 'combo'
					}, {
						areaLevel: 4,
						disabled: false,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Населенный пункт'),
						hiddenName: 'KLTown_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var form = combo.ownerCt.ownerCt.ownerCt;

								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									form.loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
								if (record.get('KLArea_id') == combo.getValue())
								{
									combo.collapse();
									return false;
								}
								combo.fireEvent('change', combo, record.get('KLArea_id'));
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'KLArea_id', type: 'int'},
								{name: 'KLArea_Name', type: 'string'}
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{KLArea_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLArea_id',
						width: 300,
						xtype: 'combo'
					}, {
						disabled: false,
						displayField: 'KLStreet_Name',
						enableKeyEvents: true,
						fieldLabel: langs('Улица'),
						hiddenName: 'KLStreet_id',
						listeners: {
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.clearValue();
								}
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'KLStreet_id', type: 'int'},
								{name: 'KLStreet_Name', type: 'string'}
							],
							key: 'KLStreet_id',
							sortInfo: {
								field: 'KLStreet_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{KLStreet_Name}',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLStreet_id',
						width: 300,
						xtype: 'combo'
					}, {
						disabled: false,
						fieldLabel: langs('Дом'),
						name: 'Address_House',
						width: 100,
						xtype: 'textfield'
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					title: langs('5. Пользователь'),

					items: [{
						autoHeight: true,
						style: 'padding: 0px;',
						title: langs('Добавление'),
						width: 755,
						xtype: 'fieldset',

						items: [{
							fieldLabel: langs('Дата'),
							name: 'InsDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							xtype: 'swdatefield'
						}, {
							fieldLabel: langs('Диапазон дат'),
							name: 'InsDate_Range',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							width: 170,
							xtype: 'daterangefield'
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: langs('Изменение'),
						width: 755,
						xtype: 'fieldset',

						items: [{
							fieldLabel: langs('Дата'),
							name: 'UpdDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							xtype: 'swdatefield'
						}, {
							fieldLabel: langs('Диапазон дат'),
							name: 'UpdDate_Range',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							width: 170,
							xtype: 'daterangefield'
						}]
					}]
				}]
			})]
		});

		sw.Promed.SearchFilterPanel.superclass.initComponent.apply(this, arguments);
	},
	labelAlign: 'right',
	labelWidth: 130,
	loadAddressCombo: function(level, country_id, value, recursion) {
		var form = this;
		var target_combo = null;

		switch (level)
		{
			case 0:
				target_combo = form.getForm().findField('KLRgn_id');
				break;

			case 1:
				target_combo = form.getForm().findField('KLSubRgn_id');
				break;

			case 2:
				target_combo = form.getForm().findField('KLCity_id');
				break;

			case 3:
				target_combo = form.getForm().findField('KLTown_id');
				break;

			case 4:
				target_combo = form.getForm().findField('KLStreet_id');
				break;

			default:
				return false;
				break;
		}

		target_combo.clearValue();
		target_combo.getStore().removeAll();
		target_combo.getStore().load({
			params: {
				country_id: country_id,
				level: level + 1,
				value: value
			},
			callback: function(store, records, options) {
				if (level >= 0 && level <= 3 && recursion == true)
				{
					form.loadAddressCombo(level + 1, country_id, value, recursion);
				}
			}
		});
	},
	tabIndexStart: 0,
	tabPanelId: null
});

/**
* Панель с возможностью добавления комбобоксов комплексных услуг
*
* @package      libs
* @access       public
* @autor		Alexander Permyakov
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      22.03.2012
*/
sw.Promed.UslugaComplexPanel = Ext.extend(Ext.Panel,
	{
		win: null,
		firstTabIndex: null,
		loadParams: null,
		baseParams: null,
		PrescriptionType_Code: null,
		UslugaComplex_Date: null,
		disabledAddUslugaComplex: false,
		autoHeight: true,
		bodyBorder: false,
		border: false,
		frame: false,
		header: false,
		labelAlign: 'right',
		labelWidth: 120,
		layout: 'form',
		lastItemsIndex: 0,
		limitCountCombo: 8,
		buttonAlign: 'right',
		isStomUslugaComplexPanel: false,
		extendedMode: false,
		showFSIDICombo: false,
		collectAllData: function() {

			var panel = this, res = [];
			panel.items.each(function(item, index, length) {

				var gpc = panel.findById('GenericPanel_UslugaComplex_Col_' + index);
				var uslugaCombo = gpc.getComponent(0).getComponent(0);

				var panelObject = {
					UslugaComplex_id: uslugaCombo.getValue(),
					Evn_id: (uslugaCombo.Evn_id) ? uslugaCombo.Evn_id : null,
					completed: uslugaCombo.disabled
				}

				if (panel.extendedMode) {

					var issueDate = gpc.getComponent(1).getComponent(0), // дата
						rejectionFlag = gpc.getComponent(2).getComponent(0); // отказ

					panelObject.issueDate = issueDate.getValue();
					panelObject.rejectionFlag = rejectionFlag.getValue();
				}

				if (panel.isStomUslugaComplexPanel) {

					var toothPanel = item.getComponent(1); // зубная панель
					panelObject.toothData = toothPanel.getData();
				}

				if (panel.showFSIDICombo) {
					panelObject.FSIDI_id = panel.findById('FSIDI_id_' + index).getValue();
				}

				res.push(panelObject);
			});

			return res;
		},
		setDisabledExtendedFields: function(disable, gpcIndex) {

			var panel = this,
				genericPanelCol = panel.findById('GenericPanel_UslugaComplex_Col_' + gpcIndex);

			genericPanelCol.items.each(function(item, index, length) {

				if (index > 0) {

					var component = item.getComponent(0);

					if (disable) {
						component.hideContainer();
						component.disable();
					} else {
						//component.hide();
						component.showContainer();
						component.enable();
					}
				}
			});
		},
		fillExendedFields: function(data, gpcIndex) {

			var panel = this,
				genericPanelCol = panel.findById('GenericPanel_UslugaComplex_Col_' + gpcIndex);

			var issueDate = genericPanelCol.getComponent(1).getComponent(0);
			var rejectionFlag = genericPanelCol.getComponent(2).getComponent(0);

			if (issueDate && data.issueDate) issueDate.setValue(data.issueDate);
			if (rejectionFlag && data.rejectionFlag) rejectionFlag.setValue(data.rejectionFlag);
		},
		collectToothData: function() {

			var panel = this; var uslugaTooths = [];

			panel.items.each(function(item, index, length) {

				var genericPanelCol = panel.findById('GenericPanel_UslugaComplex_Col_' + index),
					toothPanel = item.getComponent(1); // зубная панель

				var uslugaCombo = genericPanelCol.getComponent(0).getComponent(0);

				if (toothPanel && !uslugaCombo.disabled && uslugaCombo.getValue()) {
					uslugaTooths.push(toothPanel.getData());
				}
			});

			return uslugaTooths;
		},
		fillToothPanel: function(params) {

			if (typeof params != 'object') {params = new Object();}

			var panel = this;
			var toothPanel = panel.findById(panel.id + '_' + params.genericPanelIndex + '_' + 'ToothNumFieldsPanel')

			if (params.toothNums && toothPanel) {

				var toothList = params.toothNums.split(',');
				if (toothList) {

					toothList.forEach(function(tth, k){

						var toothNum = parseInt(tth.trim());

						if (k > 0) { toothPanel.addField()}
						toothPanel.setValueByIndex(k, toothNum);
					})

					if (params.disableFields) toothPanel.disableAll();
				}
			}
		},
		switchToStomUslugaComplexPanel: function(isStomPanel) { this.isStomUslugaComplexPanel = isStomPanel; },
		getValues: function() {

			var panel = this, res = [];
			panel.items.each(function(item, index, length) {

				var genericPanelCol = panel.findById('GenericPanel_UslugaComplex_Col_' + index);

				var uslugaCombo = genericPanelCol.getComponent(0).getComponent(0);
				if (uslugaCombo.getValue()) { res.push(uslugaCombo.getValue()); }
			});

			return res;
		},
		getUslugaComboTextValues: function(byIndex, separator) {

			if (!separator) separator = "<br />";

			var panel = this, res = '';
			panel.items.each(function(item, index, length) {

				var genericPanelCol = panel.findById('GenericPanel_UslugaComplex_Col_' + index);
				var uslugaCombo = genericPanelCol.getComponent(0).getComponent(0);

				if (index > 0) { res += separator; }

				if (byIndex && byIndex == index) {
					if (uslugaCombo.getValue()) { res = uslugaCombo.lastSelectionText; }
				} else if (!byIndex) {
					if (uslugaCombo.getValue()) { res += uslugaCombo.lastSelectionText; }
				}
			});

			return res;
		},
		getFirstCombo: function() { return this.firstCombo; },
		// setValuesFuncDiag и getValuesFuncDiag - альтернативные функции, с логикой для функ.диагностики.
		setValuesFuncDiag: function(uslugaList) {

			var panel = this, data = {};
			if (!uslugaList || !Ext.isArray(uslugaList)) { panel.setValues([null]); return; }

			var callback = function(uslugaCombo){

				var key = panel.lastValueIndex,
					usluga = uslugaList[key];

				if (usluga) {

					if (panel.isStomUslugaComplexPanel && usluga.ToothNums) {

						panel.syncSize(); panel.doLayout();

						panel.fillToothPanel({
							genericPanelIndex: panel.lastValueIndex,
							toothNums: usluga.ToothNums,
							disableFields: usluga.disabled
						});
					}
					uslugaCombo.setValue(usluga.UslugaComplex_id);
					uslugaCombo.Evn_id = usluga.Evn_id;

					if (usluga.disabled) {

						uslugaCombo.disable();
						if (panel.extendedMode) {
							panel.setDisabledExtendedFields(false, panel.lastValueIndex)
							panel.fillExendedFields(usluga, panel.lastValueIndex);
						}
						panel.buttons[0].disable();
					}  else {
						if (panel.extendedMode) { panel.setDisabledExtendedFields(true, panel.lastValueIndex) }
					}

					if (panel.showFSIDICombo) {
						var fsidiCombo = panel.findById('FSIDI_id_' + panel.lastValueIndex);
						fsidiCombo.setValue(usluga.FSIDI_id);
						if(usluga.UslugaComplex_id){
							fsidiCombo.checkVisibilityAndGost(usluga.UslugaComplex_id);
						}

					}

					panel.lastValueIndex++;
					if (uslugaList[panel.lastValueIndex]) { log('addComboLast'); panel.addCombo(false, callback); }
				}

			}.createDelegate(this);

			panel.lastValueIndex = 0;
			panel.firstCombo = panel.reset(callback);
		},
		getValuesFuncDiag: function() {

			var panel = this, res = [];
			panel.items.each(function(item, index, length) {

				var genericPanelCol = panel.findById('GenericPanel_UslugaComplex_Col_' + index);
				var uslugaCombo = genericPanelCol.getComponent(0).getComponent(0);

				if (!uslugaCombo.disabled && uslugaCombo.getValue()) {
					res.push(uslugaCombo.getValue());
				}
			});

			return res;
		},
		getEvnValuesFuncDiag: function(isCompletedUsluga) {

			var panel = this, res = [];
			panel.items.each(function(item, index, length) {

				var genericPanelCol = panel.findById('GenericPanel_UslugaComplex_Col_' + index);
				var uslugaCombo = genericPanelCol.getComponent(0).getComponent(0);

				if ((!uslugaCombo.disabled
					|| (isCompletedUsluga && uslugaCombo.disabled))
					&& uslugaCombo.Evn_id
				) { res.push(uslugaCombo.Evn_id); }
			});

			return res;
		},
		reset: function(callback) {

			var panel = this;

			panel.items.each(function(item, index, length) { panel.remove(item, true); });
			return panel.addCombo(true, callback);
		},
		setValues: function(values_arr) {

			var panel = this;
			if (!values_arr || !Ext.isArray(values_arr)) values_arr = [null];

			var callback = function(combo){

				if (values_arr[panel.lastValueIndex]) {
					combo.setValue(values_arr[panel.lastValueIndex]);
					panel.lastValueIndex++;
				}

			}.createDelegate(this);

			panel.lastValueIndex = 0;
			panel.loadParams = {};

			if (values_arr[panel.lastValueIndex]) {
				panel.loadParams = {UslugaComplex_id: values_arr[panel.lastValueIndex]};
			}

			panel.firstCombo = panel.reset(callback);

			for (var i=0; i<values_arr.length; i++) {

				if (i>0) {
					panel.loadParams = (values_arr[i]) ? {UslugaComplex_id: values_arr[i]} : {};
					panel.addCombo(false, callback);
				}
			}
		},
		setUslugaComplexDate: function(value) {

			var panel = this;
			panel.UslugaComplex_Date = value || null;

			panel.items.each(function(item, index, length) {

				var genericPanelCol = panel.findById('GenericPanel_UslugaComplex_Col_' + index);
				var uslugaCombo = genericPanelCol.getComponent(0).getComponent(0);

				if (typeof uslugaCombo.setUslugaComplexDate == 'function') {
					uslugaCombo.setUslugaComplexDate(panel.UslugaComplex_Date);
				};
			});
		},
		disable: function() {
			var panel = this;
			panel.items.each(function(item, index, length) {
				var genericPanelCol = panel.findById('GenericPanel_UslugaComplex_Col_' + index);
				var uslugaCombo = genericPanelCol.getComponent(0).getComponent(0);

				uslugaCombo.setDisabled(true);
			});
		},
		enable: function() {
			var panel = this;
			panel.items.each(function(item, index, length) {
				var genericPanelCol = panel.findById('GenericPanel_UslugaComplex_Col_' + index);
				var uslugaCombo = genericPanelCol.getComponent(0).getComponent(0);

				uslugaCombo.setDisabled(false);
			});
		},
		onChange: Ext.emptyFn,
		addCombo: function(is_first, callback) {

			var panel = this;
			if (is_first) panel.lastItemsIndex = 0; else panel.lastItemsIndex++;

			if (panel.lastItemsIndex > panel.limitCountCombo) {

				if (panel.buttons && panel.buttons[0]) { panel.buttons[0].setDisabled(true); }
				return false;

			} else { if (panel.buttons && panel.buttons[0]) { panel.buttons[0].setDisabled(false); }}

			var comboConfig = {
				allowBlank: (is_first) ? false : true,
				value: null,
				listeners: {'change': function(combo,value) {
					panel.onChange();
					if (panel.showFSIDICombo && combo.value) {
						panel.findById('FSIDI_id_' + combo.lastItemsIndex).checkVisibilityAndGost(combo.value);
					}
				} },
				fieldLabel: langs('Услуга'),
				hiddenName: 'UslugaComplex_id' + panel.lastItemsIndex,
				anchor:'98%',
				lastItemsIndex: panel.lastItemsIndex
			};

			var uslugaCombo = new Object();
			log('panel.PrescriptionType_Code', panel.PrescriptionType_Code)

			if (panel.firstTabIndex) { comboConfig.tabIndex = panel.firstTabIndex + panel.lastItemsIndex; }
			if (panel.PrescriptionType_Code) {

				comboConfig.PrescriptionType_Code = panel.PrescriptionType_Code;

				uslugaCombo = new sw.Promed.SwUslugaComplexEvnPrescrCombo(comboConfig);
				uslugaCombo.setUslugaComplexDate(panel.UslugaComplex_Date);

				panel.baseParams = uslugaCombo.getBaseParams();

			} else {

				comboConfig.onTrigger2Click = function() {

					var trigger = this;
					if (trigger.disabled) { return; }

					trigger.clearValue();
					trigger.fireEvent('change', trigger, trigger.getValue());
				};

				uslugaCombo = new sw.Promed.SwUslugaComplexPidCombo(comboConfig);
			}

			var genericPanel = panel.add({
				xtype: 'panel',
				layout: 'form',
				id: 'GenericPanel_UslugaComplex_' + panel.lastItemsIndex,
				labelWidth: (panel.labelWidth) ? panel.labelWidth : 150,
				border: false,
				frame: false,
				items:[]
			});

			var genericPanelCol = genericPanel.add({
				layout: 'column',
				border: false,
				id: 'GenericPanel_UslugaComplex_Col_' + panel.lastItemsIndex,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: (panel.labelWidth) ? panel.labelWidth : 150,
					columnWidth: (panel.extendedMode) ? .5 : .99 ,
					items: [uslugaCombo]
				}]
			});

			// если случай лечения стоматологический, в показываем номера зубов
			if (panel.isStomUslugaComplexPanel) {
				genericPanel.add({
					ownerWindow: panel.win,
					xtype: 'swduplicatedfieldpanel',
					fieldLbl: 'Номер зуба',
					fieldName: 'ToothNumEvnUsluga_ToothNum',
					id: panel.id + '_' + panel.lastItemsIndex + '_' + 'ToothNumFieldsPanel',
					fullScreenWnd: true,
					labelWidth: 150
				});
			}

			// для Карелии и ЕКБ, показываем дату и чекбокс, после выполнения услуги
			if (panel.extendedMode) {
				genericPanelCol.add(
					{
						layout: 'form',
						border: false,
						labelWidth: 150,
						columnWidth: .3,
						items: [new sw.Promed.SwDateField({
							allowBlank: true,
							fieldLabel: langs('Дата выдачи справки'),
							labelWidth: 150,
							hiddenName: 'UslugaComplex_Date' + panel.lastItemsIndex,
							width: 100,
							hidden: true,
							disabled: true
						})]
					},{
						layout: 'form',
						border: false,
						labelWidth: 150,
						columnWidth: .2,
						items: [{
							xtype:'checkbox',
							hideLabel:true,
							id: 'PrintCost_NoPrint' + panel.lastItemsIndex,
							hiddenName: 'PrintCost_NoPrint' + panel.lastItemsIndex,
							boxLabel: langs('отказ в получении справки'),
							checked: false,
							width: 190,
							hidden: true,
							disabled: true
						}]
					}
				);
			}

			if (panel.showFSIDICombo) {
				genericPanel.add({
					ownerWindow: panel.win,
					xtype: 'swfsidicombo',
					id: 'FSIDI_id_' + panel.lastItemsIndex,
					width: 500,
					labelWidth: 250,
					hideOnInit: true,
					listWidth: 500,
				});
			}

			panel.syncSize(); panel.doLayout();
			if (panel.win) { panel.win.syncSize(); panel.win.doLayout() }

			panel.loadSpr(uslugaCombo, 'UslugaComplex_id', panel.loadParams, callback);
			return uslugaCombo;
		},
		loadSpr: function(combo, field_value, params, callback)
		{

			var value = combo.getValue();	
			combo.getStore().removeAll();
			combo.getStore().baseParams = this.baseParams;
			combo.getStore().load(
			{
				params: params,
				callback: function() 
				{
					if (combo && typeof combo.getStore == 'function' && combo.getStore()) {

						combo.getStore().each(function (record) {
							if (record.data[field_value] == value) {
								combo.setValue(value);
								combo.fireEvent('select', combo, record, 0);
							}
						});

						if (callback) { callback(combo); } // вызываем setValues()
					}
				}
			});
		},
		initComponent: function()
		{
			var panel = this;

			if (!panel.disabledAddUslugaComplex) {

				var addBtnConfig = {
					iconCls: 'add16',
					text: langs('Добавить услугу'),
					handler: function() {
						this.addCombo(false, function() {
							panel.setDisabledExtendedFields(true, panel.lastItemsIndex);
						}); }.createDelegate(this)
				};
				
				if (panel.buttonLeftMargin) { addBtnConfig.style = 'margin-left: '+ panel.buttonLeftMargin +'px;'; }
				if (panel.firstTabIndex) { addBtnConfig.tabIndex = panel.firstTabIndex + 11; }

				this.buttons = [addBtnConfig];
			}

			if (typeof panel.win != 'object') panel.win = false;
			if (typeof panel.loadParams != 'object') panel.loadParams = {};
			if (typeof panel.baseParams != 'object') panel.baseParams = {level:0};

			sw.Promed.UslugaComplexPanel.superclass.initComponent.apply(this, arguments);
		}
	}
);
Ext.reg('swuslugacomplexpanel', sw.Promed.UslugaComplexPanel);

/**
* Панель со списком диагнозов
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
*/
sw.Promed.DiagListPanel = Ext.extend(Ext.Panel, {
	win: null,
	firstTabIndex: null,
	loadParams: null,
	baseParams: null,
	PrescriptionType_Code: null,
	disabledAddDiag: false,
	autoHeight: true,
	bodyBorder: false,
	border: false,
	frame: false,
	header: false,
	labelAlign: 'right',
	labelWidth: 120,
	layout: 'form',
	lastItemsIndex: 0,
	buttonAlign: 'right',
	fieldLabel: 'Диагноз',
	disabled: false,
	fieldWidth: 350,
	width: 700,
	deleteCombo: function(index) {
		var win = this;
		if (this.disabled) return false;
		if (this.findById(this.id+'Diag_id'+index).isFirst) {
			var combo = this.findById(this.id+'Diag_id'+index).items.items[0].items.items[0];

			//Найдем следующий элемент
			var next_item = new Object();
			var next_item_exists = false;
			this.items.each(function(cur_item){
				if(!cur_item.isFirst && !next_item_exists)
				{
					next_item = cur_item;
					next_item_exists = true;
				}
			});
			if(!next_item_exists)
			{
				combo.clearValue();
			}
			else
			{
				next_item.isFirst = true;
				next_item.items.items[0].items.items[0].labelSeparator = ':';
				next_item.items.items[0].items.items[0].setFieldLabel(this.fieldLabel);
				this.remove(this.findById(this.id+'Diag_id'+index),true);
			}
		} else {
			this.remove(this.findById(this.id+'Diag_id'+index),true);
		}
	},
	getValues: function() {
		var res = [];
		this.items.each(function(item,index,length) {
			var combo = item.items.items[0].items.items[0];
			if(combo.getValue()) {
				res.push(combo.getValue());
			}
		},this);
		return res;
	},
	getFirstCombo: function() {
		return this.firstCombo;
	},
	reset: function(callback) {
		this.items.each(function(item,index,length) {
			this.remove(item,true);
		},this);
		return this.addCombo(true,callback);
	},
	setValues: function(values_arr) {
		if(!values_arr || !Ext.isArray(values_arr))
			values_arr = [null];
		var callback = function(combo) {
			if(values_arr[this.lastValueIndex]) {
				combo.setValue(values_arr[this.lastValueIndex]);
				this.lastValueIndex++;
			}
		}.createDelegate(this);
		this.lastValueIndex = 0;
		this.loadParams = {};
		if(values_arr[this.lastValueIndex]) {
			this.loadParams = {Diag_id: values_arr[this.lastValueIndex]};
		}
		this.firstCombo = this.reset(callback);
		for(var i=0;i<values_arr.length;i++) {
			if(i>0) {
				this.loadParams = (values_arr[i]) ? {Diag_id: values_arr[i]} : {};
				this.addCombo(false,callback);
			}
		}
	},
	disable: function() {
		this.disabled = true;
		this.items.each(function(item,index,length) {
			var combo = item.items.items[0].items.items[0];
			combo.disable();
		},this);
	},
	enable: function() {
		this.disabled = false;
		this.items.each(function(item,index,length) {
			var combo = item.items.items[0].items.items[0];
			combo.enable();
		},this);
	},
	onChange: Ext.emptyFn,
	addCombo: function(is_first,callback) {
		if (this.disabled) return false;
		var panel = this;
		if(is_first)
			this.lastItemsIndex = 0;
		else
			this.lastItemsIndex++;
		var conf_combo = {
			allowBlank: true,
			value: null,
			listeners: {
				'change': function() {
					panel.onChange();
				}
			},
			fieldLabel: is_first ? this.fieldLabel : null,
			labelSeparator: is_first ? ':' : '',
			hiddenName: 'Diag_id'+this.lastItemsIndex,
			width: this.fieldWidth
		};
		if(this.firstTabIndex) {
			conf_combo.tabIndex = this.firstTabIndex + this.lastItemsIndex;
		}
		conf_combo.onTrigger2Click = function() {
			if (this.disabled) {
				return;
			}
			this.clearValue();
			this.fireEvent('change', this, this.getValue());
		};

		var combo = new sw.Promed.SwDiagCombo(conf_combo);
		var cp = new Ext.Panel({
			id:	this.id + 'Diag_id' + this.lastItemsIndex,
			layout: 'column',
			height: 25,
			width: this.width,
			border: false,
			autowidth: true,
			hideLabel: false,
			isFirst: is_first,
			defaults: {
				border: false,
				bodyStyle: 'background: transparent;'
			},
			items:[
				new Ext.Panel({
					layout: 'form',
					height: 25,
					labelWidth: this.labelWidth,
					hideLabel: false,
					width: (this.width - 160),
					items:[combo]
				}),
				new Ext.Panel({
					height: 25,
					width: 100,
					style: 'margin: 2px 3px 0;',
					html: '<a href="#" onclick="Ext.getCmp(\''+this.id+'\').deleteCombo(\''+this.lastItemsIndex+'\');">Удалить</a>'
				})
			]
		});
		var cb = this.add(cp);
		this.syncSize();
		this.doLayout();
		if(this.win) this.win.syncSize();
		this.loadSpr(cb,'Diag_id', this.loadParams,callback);
		return cb;
	},
	loadSpr: function(panel, field_value, params, callback)
	{
		var combo = panel.items.items[0].items.items[0];
		if (callback) {
			callback(combo);
		}
		var value = combo.getValue();
		if (!Ext.isEmpty(value)) {
			combo.getStore().load({
				callback: function(){
					combo.getStore().each(function(rec){
						if(rec.get('Diag_id') == value) {
							combo.setValue(value);
							combo.fireEvent('select', combo, rec, 0);
						}
					});
				},
				params: { where: "where Diag_id = " + value }
			});
		}
	},
	initComponent: function()
	{
		if (!this.disabledAddDiag) {
			var conf_add_btn = new Ext.Panel({
				height: 20,
				width: 100,
				border: false,
				style: 'margin: -10px 0 0;',
				html: '<a href="#" onclick="Ext.getCmp(\''+this.id+'\').addCombo();">Добавить</a>'
			});
			
			if (this.buttonLeftMargin) {
				conf_add_btn.style += 'margin-left: '+ this.buttonLeftMargin +'px;';
			}
			this.buttons = [conf_add_btn];
		}
		if(typeof this.win != 'object')
			this.win = false;
		if(typeof this.loadParams != 'object')
			this.loadParams = {};
		if(typeof this.baseParams != 'object')
			this.baseParams = {level:0};

		sw.Promed.DiagListPanel.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swdiaglistpanel', sw.Promed.DiagListPanel);

/**
* Панель со списком диагнозов
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
*/
sw.Promed.DiagListPanel2 = Ext.extend(Ext.Panel, {
	win: null,
	firstTabIndex: null,
	loadParams: null,
	baseParams: null,
	PrescriptionType_Code: null,
	disabledAddDiag: false,
	autoHeight: true,
	bodyBorder: false,
	border: false,
	frame: false,
	header: false,
	labelAlign: 'right',
	labelPad: 5,
	labelWidth: 120,
	layout: 'form',
	lastItemsIndex: 0,
	buttonAlign: 'right',
    minButtonWidth: 25,
	fieldLabel: 'Диагноз',
	disabled: false,
	fieldWidth: 350,
	width: 700,
	deleteCombo: function(index) {
		var win = this;
		if (this.disabled) return false;
		if (this.findById(this.id+'Diag_id'+index).isFirst) {
			var combo = this.findById(this.id+'Diag_id'+index).items.items[0].items.items[0];

			//Найдем следующий элемент
			var next_item = new Object();
			var next_item_exists = false;
			this.items.each(function(cur_item){
				if(!cur_item.isFirst && !next_item_exists)
				{
					next_item = cur_item;
					next_item_exists = true;
				}
			});
			if(!next_item_exists)
			{
				combo.clearValue();
			}
			else
			{
				next_item.isFirst = true;
				next_item.items.items[0].items.items[0].labelSeparator = ':';
				next_item.items.items[0].items.items[0].setFieldLabel(this.fieldLabel);
				this.remove(this.findById(this.id+'Diag_id'+index),true);
			}
		} else {
			this.remove(this.findById(this.id+'Diag_id'+index),true);
		}
	},
	getValues: function() {
		var res = [];
		this.items.each(function(item,index,length) {
			var combo = item.items.items[0].items.items[0];
			if(combo.getValue()) {
				res.push(combo.getValue());
			}
		},this);
		return res;
	},
	getFirstCombo: function() {
		return this.firstCombo;
	},
	reset: function(callback) {
		this.items.each(function(item,index,length) {
			this.remove(item,true);
		},this);
		return this.addCombo(true,callback);
	},
	setValues: function(values_arr) {
		if(!values_arr || !Ext.isArray(values_arr))
			values_arr = [null];
		var callback = function(combo) {
			if(values_arr[this.lastValueIndex]) {
				combo.setValue(values_arr[this.lastValueIndex]);
				this.lastValueIndex++;
			}
		}.createDelegate(this);
		this.lastValueIndex = 0;
		this.loadParams = {};
		if(values_arr[this.lastValueIndex]) {
			this.loadParams = {Diag_id: values_arr[this.lastValueIndex]};
		}
		this.firstCombo = this.reset(callback);
		for(var i=0;i<values_arr.length;i++) {
			if(i>0) {
				this.loadParams = (values_arr[i]) ? {Diag_id: values_arr[i]} : {};
				this.addCombo(false,callback);
			}
		}
	},
	disable: function() {
		this.disabled = true;
		this.items.each(function(item,index,length) {
			var combo = item.items.items[0].items.items[0];
			combo.disable();
		},this);
	},
	enable: function() {
		this.disabled = false;
		this.items.each(function(item,index,length) {
			var combo = item.items.items[0].items.items[0];
			combo.enable();
		},this);
	},
	onChange: Ext.emptyFn,
	addCombo: function(is_first,callback) {
		if (this.disabled) return false;
		var panel = this;
		if(is_first)
			this.lastItemsIndex = 0;
		else
			this.lastItemsIndex++;
		var ItemsIndex = this.lastItemsIndex;
		var conf_combo = {
			allowBlank: true,
			value: null,
			listeners: {
				'change': function() {
					panel.onChange();
				}
			},
			fieldLabel: is_first ? this.fieldLabel : null,
			labelSeparator: is_first ? ':' : '',
			hiddenName: 'Diag_id'+this.lastItemsIndex,
			width: this.fieldWidth
		};
		if(this.firstTabIndex) {
			conf_combo.tabIndex = this.firstTabIndex + this.lastItemsIndex;
		}
		conf_combo.onTrigger2Click = function() {
			if (this.disabled) {
				return;
			}
			this.clearValue();
			this.fireEvent('change', this, this.getValue());
		};

		var combo = new sw.Promed.SwDiagCombo(conf_combo);
		var cp = new Ext.Panel({
			id:	this.id + 'Diag_id' + this.lastItemsIndex,
			layout: 'column',
			width: this.width,
			border: false,
			autowidth: true,
			hideLabel: false,
			isFirst: is_first,
			defaults: {
				border: false,
				bodyStyle: 'background: transparent;'
			},
			items:[
				new Ext.Panel({
					layout: 'form',
					labelPad: this.labelPad,
					labelWidth: this.labelWidth,
					hideLabel: false,
					width: (this.width - 170),
					items:[combo]
				}),
				new Ext.Panel({
					height: 25,
					width: 50,
					style: 'margin: 17px 3px 0;',
					items:[{
						handler: function() {
							this.deleteCombo(ItemsIndex)
						}.createDelegate(this),
						xtype: 'tbbutton',
						iconCls: 'delete16',
						tooltip: langs('Удалить')
					}]
				})
			]
		});
		var cb = this.add(cp);
		this.syncSize();
		this.doLayout();
		if(this.win) this.win.syncSize();
		this.loadSpr(cb,'Diag_id', this.loadParams,callback);
		return cb;
	},
	loadSpr: function(panel, field_value, params, callback)
	{
		var combo = panel.items.items[0].items.items[0];
		if (callback) {
			callback(combo);
		}
		var value = combo.getValue();
		if (!Ext.isEmpty(value)) {
			combo.getStore().load({
				callback: function(){
					combo.getStore().each(function(rec){
						if(rec.get('Diag_id') == value) {
							combo.setValue(value);
							combo.fireEvent('select', combo, rec, 0);
						}
					});
				},
				params: { where: "where Diag_id = " + value }
			});
		}
	},
	initComponent: function()
	{
		if (!this.disabledAddDiag) {
			var conf_add_btn = {
				xtype: 'tbbutton',
				minButtonWidth: 25,
				style: 'margin: -36px 305px 0px; position: relative;',
				handler: function() {
					this.addCombo();
				}.createDelegate(this),
				iconCls: 'add16',
				tooltip: langs('Добавить')
			};
			this.buttons = [conf_add_btn];
		}
		if(typeof this.win != 'object')
			this.win = false;
		if(typeof this.loadParams != 'object')
			this.loadParams = {};
		if(typeof this.baseParams != 'object')
			this.baseParams = {level:0};

		sw.Promed.DiagListPanel2.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swdiaglistpanel2', sw.Promed.DiagListPanel2);

/**
* Панель со списком диагнозов ver.2 - добавил текстовое описание диагноза
*/
sw.Promed.DiagListPanelWithDescr = Ext.extend(Ext.Panel, {
	win: null,
	firstTabIndex: null,
	loadParams: null,
	baseParams: null,
	PrescriptionType_Code: null,
	disabledAddDiag: false,
	autoHeight: true,
	bodyBorder: false,
	border: false,
	frame: false,
	header: false,
	labelAlign: 'right',
	labelWidth: 120,
	layout: 'form',
	lastItemsIndex: 0,
	buttonAlign: 'left',
	fieldLabel: 'Диагноз',
	disabled: false,
	fieldWidth: 400,
	width: 1600,
	showOsl: false,
	showDescr: true,
	deleteCombo: function(index) {
		var win = this;
		if (this.disabled) return false;
		if (this.findById(this.id+'Diag_id'+index).isFirst) {
			var combo = this.findById(this.id+'Diag_id'+index).items.items[0].items.items[0];
			var combo_descr = this.findById(this.id+'Diag_id'+index).items.items[1].items.items[0];

			var next_item = new Object();
			var next_item_exists = false;
			this.items.each(function(cur_item){
				if(!cur_item.isFirst && !next_item_exists)
				{
					next_item = cur_item;
					next_item_exists = true;
				}
			});
			if(!next_item_exists)
			{
				combo.clearValue();
				combo_descr.setValue('');
				Ext.QuickTips.unregister(combo_descr.getEl());
				combo_descr.disable();
			}
			else
			{
				next_item.isFirst = true;
				next_item.items.items[0].items.items[0].labelSeparator = ':';
				next_item.items.items[0].items.items[0].setFieldLabel(this.fieldLabel);
				this.remove(this.findById(this.id+'Diag_id'+index),true);
			}
		} else {
			this.remove(this.findById(this.id+'Diag_id'+index),true);
		}


	},
	getValues: function() {
		var res = new Array();
		this.items.each(function(item,index,length) {
			var res_it = new Array();
			var combo = item.items.items[0].items.items[0];
			var combo_descr = item.items.items[1].items.items[0];
			var combo_sub = (this.showOsl) ? item.items.items[2].items.items[0].getValues() : [];
			if(combo.getValue()) {
				res_it.push(combo.getValue());
				res_it.push(combo_descr.getValue());
				res_it.push(combo_sub);
				res.push(res_it);
			}
		},this);
		return res;
	},
	getFirstCombo: function() {
		return this.firstCombo;
	},
	reset: function(callback) {
		this.items.each(function(item,index,length) {
			this.remove(item,true);
		},this);
		return this.addCombo(true,callback);
	},
	setValues: function(values_arr) {
		if(!values_arr || !Ext.isArray(values_arr))
			values_arr = [null];

		var callback = function(combo,combo_decr) {
			if(values_arr[this.lastValueIndex]) {
				combo.setValue(values_arr[this.lastValueIndex].Diag_id);
				Ext.getCmp(this.id + 'Diag_decr'+[this.lastValueIndex]).setValue(values_arr[this.lastValueIndex].DescriptDiag);
				Ext.getCmp(this.id + 'Diag_decr'+[this.lastValueIndex]).enable();
				Ext.QuickTips.register({
					target: Ext.getCmp(this.id + 'Diag_decr'+[this.lastValueIndex]).getEl(),
					text: values_arr[this.lastValueIndex].DescriptDiag,
					enabled: true,
					showDelay: 5,
					trackMouse: true,
					autoShow: true
				});	
				if (this.showOsl) {
					combo_osl = Ext.getCmp(this.id + 'Diag_osl'+[this.lastValueIndex]);
					combo_osl.enable();
					if (values_arr[this.lastValueIndex].OslDiag) {
						combo_osl.setValues(values_arr[this.lastValueIndex].OslDiag);
					}
				}
				this.lastValueIndex++;
			}
		}.createDelegate(this);
		this.lastValueIndex = 0;
		this.loadParams = {};
		if(values_arr[this.lastValueIndex]) {
			this.loadParams = {Diag_id: values_arr[this.lastValueIndex].Diag_id};
		}
		this.firstCombo = this.reset(callback);
		for(var i=0;i<values_arr.length;i++) {
			if(i>0) {
				this.loadParams = (values_arr[i].Diag_id) ? {Diag_id: values_arr[i].Diag_id} : {};
				this.addCombo(false,callback);
			}
		}
	},
	disable: function() {
		this.disabled = true;
		this.items.each(function(item,index,length) {
			var combo = item.items.items[0].items.items[0];
			var combo_descr = item.items.items[1].items.items[0];
			combo.disable();
			combo_descr.disable();
		},this);
	},
	enable: function() {
		this.disabled = false;
		this.items.each(function(item,index,length) {
			var combo = item.items.items[0].items.items[0];
			var combo_descr = item.items.items[1].items.items[0];
			combo.enable();
			combo_descr.enable();
		},this);
	},
	onChange: Ext.emptyFn,
	addCombo: function(is_first,callback) {
		if (this.disabled) return false;
		var panel = this;
		if(is_first)
			this.lastItemsIndex = 0;
		else
			this.lastItemsIndex++;
		var conf_combo = {
			allowBlank: true,
			value: null,
			bodyStyle: 'padding-left: 0 !important;',
			onChange: function(combo, value) {
				combo_descr = combo.ownerCt.ownerCt.items.items[1].items.items[0];
				combo_osl = panel.showOsl ? combo.ownerCt.ownerCt.items.items[2].items.items[0] : null;
				if(Ext.isEmpty(value))
				{
					combo_descr.setValue('');
					combo_descr.disable();
					Ext.QuickTips.register({
						target: combo_decr.getEl(),
						text: '',
						enabled: false,
						showDelay: 5,
						trackMouse: true,
						autoShow: true
					});	
					if (panel.showOsl) {
						combo_osl.reset();
						combo_osl.disable();
					}
				}
				else
				{
					combo_descr.enable();	
					if (panel.showOsl) {
						combo_osl.enable();
					}
				}
				panel.onChange();
			},
			fieldLabel: is_first ? this.fieldLabel : null,
			labelSeparator: is_first ? ':' : '',
			hiddenName: 'Diag_id'+this.lastItemsIndex,
			width: this.fieldWidth//-100
		};
		if(this.firstTabIndex) {
			conf_combo.tabIndex = this.firstTabIndex + this.lastItemsIndex;
		}
		conf_combo.onTrigger2Click = function() {
			if (this.disabled) {
				return;
			}
			this.clearValue();
			this.fireEvent('change', this, this.getValue());
		};

		var conf_descr = {
			allowBlank: true,
			value: '',
			hideLabel: this.fieldDescLabel ? false : true,
			disabled: true,
			hiddenName: this.id + 'Diag_decr' + this.lastItemsIndex,
			id: this.id + 'Diag_decr' + this.lastItemsIndex,
			width: this.fieldDescLabel ? 330 : 300,
			tabIndex: 999,
			emptyText: this.fieldDescLabel ? '' : 'Введите описание заболевания...',
			fieldLabel: is_first ? this.fieldDescLabel : '',
			labelSeparator: is_first && this.fieldDescLabel ? ':' : '',
			autoCreate: {
				autocomplete: "off",
				maxLength: "100",
				size: "100",
				tag: "input",
				type: "text"
			},
			listeners: {
				'change': function(combo, value) {
					Ext.QuickTips.register({
						target: combo.getEl(),
						text: value,
						enabled: true,
						showDelay: 5,
						trackMouse: true,
						autoShow: true
					});	
				}
			}
		}

		var combo = new sw.Promed.SwDiagCombo(conf_combo);
		var combo2 = new sw.Promed.DiagListPanel2({
			win: this,
			width: 450,
			buttonAlign: 'left',
			id: this.id + 'Diag_osl' + this.lastItemsIndex,
			buttonLeftMargin: 0,
			labelPad: 0,
			labelWidth: 1,
			fieldWidth: 270,
			style: 'background: transparent; margin: 0; padding: 0;',
			fieldLabel: 'Осложнения сопутствующего заболевания',
			onChange: function() {
				
			}
		});
		var combo_decr = new Ext.form.TextField(conf_descr);
		var cp = new Ext.Panel({
			id:	this.id + 'Diag_id' + this.lastItemsIndex,
			layout: 'column',
			width: this.width,
			border: false,
			autowidth: true,
			hideLabel: false,
			isFirst: is_first,
			defaults: {
				border: false,
				bodyStyle: 'background: transparent;'
			},
			items:[
				new Ext.Panel({
					layout: 'form',
					labelPad: this.labelAlign == 'top' ? 0 : 5,
					labelWidth: this.labelAlign == 'top' ? 1 : this.labelWidth,
					width: this.fieldDescLabel ? (this.labelAlign == 'top' ? 300 : (this.width - 600)) : (this.width - 560),
					items:[combo]
				}),
				new Ext.Panel({
					layout: 'form',
					labelPad: this.labelAlign == 'top' ? 0 : 5,
					labelWidth: this.labelAlign == 'top' ? 1 : this.labelWidth,
					width: this.fieldDescLabel ? 370 : 300,
					hidden: !this.showDescr,
					items:[combo_decr]
				}),
				new Ext.Panel({
					layout: 'form',
					width: 400,
					style: 'margin-bottom: -20px;',
					hidden: !this.showOsl,
					items:[combo2]
				}),
				new Ext.Panel({
					height: 25,
					width: 100,
					style: this.labelAlign == 'top'  ? 'margin: 20px 10px 0;' : 'margin: 2px 10px 0;',
					html: '<a href="#" onclick="Ext.getCmp(\''+this.id+'\').deleteCombo(\''+this.lastItemsIndex+'\');">Удалить</a>'
				})
			]
		});
		var cb = this.add(cp);
		this.syncSize();
		this.doLayout();
		if(this.win) this.win.syncSize();
		combo2.reset();
		combo2.disable();
		this.loadSpr(cb,'Diag_id', this.loadParams,callback);
		return cb;
	},
	loadSpr: function(panel, field_value, params, callback)
	{
		var combo = panel.items.items[0].items.items[0];
		if (callback) {
			callback(combo);
		}
		var value = combo.getValue();
		if (!Ext.isEmpty(value)) {
			combo.getStore().load({
				callback: function(){
					combo.getStore().each(function(rec){
						if(rec.get('Diag_id') == value) {
							combo.setValue(value);
							combo.fireEvent('select', combo, rec, 0);
						}
					});
				},
				params: { where: "where Diag_id = " + value }
			});
		}
	},
	initComponent: function()
	{
		if (!this.disabledAddDiag) {
			var conf_add_btn = new Ext.Panel({
				height: 20,
				width: 100,
				border: false,
				style: 'margin: -10px 0 0;',
				html: '<a href="#" onclick="Ext.getCmp(\''+this.id+'\').addCombo();">Добавить</a>'
			});
			
			if (this.buttonLeftMargin) {
				conf_add_btn.style += 'margin-left: '+ this.buttonLeftMargin +'px;';
			}
			this.buttons = [conf_add_btn];
		}
		if(typeof this.win != 'object')
			this.win = false;
		if(typeof this.loadParams != 'object')
			this.loadParams = {};
		if(typeof this.baseParams != 'object')
			this.baseParams = {level:0};

		sw.Promed.DiagListPanelWithDescr.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swdiaglistpanelwithdescr', sw.Promed.DiagListPanelWithDescr);



/**
* Панель с возможностью добавления файлов
*
* @package      libs
* @access       public
* @autor		Alexander Chebukin
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      14.05.2012
*/
sw.Promed.FileUploadPanel = Ext.extend(Ext.Panel,
	{
		win: null,
		firstTabIndex: null,
		loadParams: null,
		autoHeight: true,
		bodyBorder: false,
		border: false,
		frame: false,
		header: false,
		labelAlign: 'right',
		labelWidth: 120,
		layout: 'form',
		lastItemsIndex: 0,
		limitCountCombo: 8,
		listParams: null,
		buttonAlign: 'right',
		dataUrl: null,
		saveUrl: null,
		saveChangesUrl: null,
		deleteUrl: null,
		fieldsPrefix: 'EvnMediaData',
		commentTextfieldWidth: 450,
		commentTextColumnWidth: .55,
		commentLabelWidth: 80,
		uploadFieldColumnWidth: .4,
		folder: null,
		getChangedData: function(){ //возвращает новые и измненные показатели
			var current_window = this;
			var data = new Array();
			this.FileStore.each(function(record) {				
				if ((record.data.state == 'add' )) {
					record.data[current_window.fieldsPrefix+'_Comment'] = this.findById('FileDescr' + record.data.Store_id).getValue();
					data.push(record.data);
				} else if (record.data.state == 'delete') {
					data.push(record.data);
				}
			}.createDelegate(this));
			return data;
		},
		getJSONChangedData: function(){ //возвращает новые и измненные показатели в виде закодированной JSON строки
			var dataObj = this.getChangedData();
			return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
		},
		deleteCombo: function(index) {
			var current_window = this;
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Вы хотите удалить запись?'),
				title: langs('Подтверждение'),
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						this.FileStore.findBy(function(rec) {
							if ( rec.get('Store_id') == index ) {
								if (rec.get(current_window.fieldsPrefix+'_id') == 0) {
									this.remove(rec,true);
								} else {
									rec.set('state', 'delete');
								}
							}
						});
						this.findById('Uploader'+index).hide();
						this.checkLimitCountCombo();
					}
				}.createDelegate(this)
			});
		},
		getCountCombo:  function() { //возвращает текущее количество комбо
			var cnt = 0;
			this.items.each(function(item,index,length) {
				if (!item.hidden) {
					cnt++;
				};
			});
			return cnt;
		},
		checkLimitCountCombo: function() {
			if(this.getCountCombo() >= this.limitCountCombo) {
				if (this.buttons && this.buttons[0]) {
					this.buttons[0].setDisabled(true);
				}
			} else {
				if (this.buttons && this.buttons[0] && !this.disabled) {
					this.buttons[0].setDisabled(false);
				}
			}
		},
		reset: function() {
			this.items.each(function(item,index,length) {
				this.remove(item,true);
			},this);
			this.FileStore = new Ext.data.JsonStore({
				autoLoad: false,
				url: this.dataUrl,
				fields: [
					'Store_id',
					this.fieldsPrefix+'_id',
					this.fieldsPrefix+'_FilePath',
					this.fieldsPrefix+'_FileName',
					'state',
					this.fieldsPrefix+'_FileLink',
					this.fieldsPrefix+'_Comment'
				]
			});
			this.lastItemsIndex = 0;
			this.checkLimitCountCombo();
		},
		loadData: function(params) {
			var current_window = this;
            var add_empty_combo = true; //флаг отражающий необходимость добавлять пустой комбобокс в список файлов после загрузки, если ничего не загрузилось

			if (!params || params == null)
				params = this.listParams;

            if (params.add_empty_combo != undefined) {
                add_empty_combo = params.add_empty_combo;
            }
			
			this.FileStore.load({
				params: params,
				callback: function() {
					current_window.FileStore.each(function(record) {
						record.data.Store_id = record.data[current_window.fieldsPrefix+'_id'];
						current_window.addCombo(true, record.data);
					});
					if (current_window.FileStore.getCount() == 0 && add_empty_combo) {
						current_window.addCombo(false);
					}
					if (params.callback)
						params.callback();
					current_window.checkLimitCountCombo();
				}
			});
		},
		saveChanges: function() {
			var current_window = this;
			var params = new Object();
			params = this.listParams;
			params.changedData = this.getJSONChangedData();
			params.saveOnce = true;

			Ext.Ajax.request({
				url: current_window.saveChangesUrl,
				callback: function(options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						//swalert(response_obj);
					} else {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						//swalert(response_obj);
					}
				},
				method: 'post',
				params: params
			});
		},
		loadFile: function(ItemsIndex) {
			
			var current_window = this;
			var params = this.listParams;
			var loadMask = new Ext.LoadMask(this.ownerCt.ownerCt.getEl(), {msg: LOAD_WAIT_SAVE});
			
			loadMask.show();
			
			var form = this.findById('Upload' + ItemsIndex).getForm();										
			form.submit({
				url: current_window.saveUrl,
				failure: function (form, action) {
					if ( action.result ) {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
						}
					}
					loadMask.hide();
				}.createDelegate(this),
				params: params,
				success: function(form, action) {				
					if (!action.result.data ) {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошла ошибка'));
						return false;
					}
					var response_obj = Ext.util.JSON.decode(action.result.data);
					form.reset();
					var ds_model = Ext.data.Record.create([
						'Store_id',
						current_window.fieldsPrefix+'_id',
						current_window.fieldsPrefix+'_FilePath',
						current_window.fieldsPrefix+'_FileName',
						'state',
						current_window.fieldsPrefix+'_FileLink',
						current_window.fieldsPrefix+'_Comment'
					]);
					var pos = (this.FileStore.data.first() && this.FileStore.data.first().data[this.fieldsPrefix+'_id'] != null) ? this.FileStore.data.length : 0;
					
					var new_ds_model = new ds_model();
					new_ds_model['Store_id'] = ItemsIndex;
					new_ds_model[current_window.fieldsPrefix+'_id'] = 0;
					new_ds_model[current_window.fieldsPrefix+'_FilePath'] = response_obj[0].file_name;
					new_ds_model[current_window.fieldsPrefix+'_FileName'] = response_obj[0].orig_name;
					new_ds_model['state'] = 'add';
					new_ds_model[current_window.fieldsPrefix+'_FileLink'] = '<a href="/uploads/'+this.folder+response_obj[0].file_name+'" target="_blank">'+response_obj[0].orig_name+'</a>';
					new_ds_model[current_window.fieldsPrefix+'_Comment'] = response_obj[0].description;
					
					this.FileStore.insert(
						pos,
						new ds_model(new_ds_model)
					);
					current_window.findById('Upload' + ItemsIndex).getEl().dom.innerHTML = '<p style="margin: 7px 0 0 110px; font-size: 12px;"><a href="/uploads/'+this.folder+response_obj[0].file_name+'" target="_blank">'+response_obj[0].orig_name+'</a></p>';
					current_window.findById('Upload' + ItemsIndex).render();
					current_window.checkLimitCountCombo();
					loadMask.hide();					
				}.createDelegate(this)
			});	
		},
		disable: function() {
			/*this.items.each(function(item,index,length) {
				item.disable();
			},this);*/
			var btn = this.findBy(function(obj){return obj.xtype == 'tbbutton' || obj.xtype == 'fileuploadfield' || obj.xtype == 'textfield'});
			for(var i=0; i < btn.length; i++) {
				btn[i].disable();
			}
			if (this.buttons && this.buttons[0]) {
				this.buttons[0].disable();
			}
			this.disabled = true;
		},
		enable: function() {
			/*this.items.each(function(item,index,length) {
				item.enable();
			},this);*/
			var btn = this.findBy(function(obj){return obj.xtype == 'tbbutton' || obj.xtype == 'fileuploadfield' || obj.xtype == 'textfield'});
			for(var i=0; i < btn.length; i++) {
				btn[i].enable();
			}
			if (this.buttons && this.buttons[0]) {
				this.buttons[0].enable();
			}
			this.disabled = false;
		},
		addCombo: function(is_filled, data) {
			
			if (is_filled) {
				var ItemsIndex = data[this.fieldsPrefix+'_id'];
				var fileup = new Ext.form.FormPanel({
					id: 'Upload' + ItemsIndex,
					bodyBorder: false,
					border: false,
					columnWidth: this.uploadFieldColumnWidth,
					html: '<p style="float: left; margin: 7px 10px 0 0; width: 100px; text-align: right; font-size: 12px;">Документ:</p><p style="float: left; margin-top: 7px; font-size: 12px;">'+data[this.fieldsPrefix+'_FileLink']+'</p>'
				});
				var descr = new Ext.form.FormPanel({
					layout: 'form',
					columnWidth: this.commentTextColumnWidth,
					bodyBorder: false,
					border: false,
					html: '<p style="float: left; margin: 7px 20px 0 0; width: 70px; text-align: right; font-size: 12px;">Комментарий:</p><p style="float: left; margin-top: 7px; font-size: 12px;">'+data[this.fieldsPrefix+'_Comment']+'</p>'
				});
			} else {
				this.lastItemsIndex--;
				var ItemsIndex = this.lastItemsIndex;
				var fileup = new Ext.form.FormPanel({
					id: 'Upload' + ItemsIndex,
					layout: 'form',
					fileUpload: true,
					columnWidth: this.uploadFieldColumnWidth,
					items: [{
						allowBlank: true,
						id: 'FileUpload' + ItemsIndex,
						fieldLabel: langs('Документ'),
						name: 'userfile',
						buttonText: langs('Выбрать'),
						xtype: 'fileuploadfield',
						listeners: {
							'fileselected': function(field, value){
								this.loadFile(ItemsIndex);
							}.createDelegate(this)
						},
						width: 250
					}]
				});
				var descr = {
					layout: 'form',
					columnWidth: this.commentTextColumnWidth,
					labelWidth: this.commentLabelWidth,
					items:[{
						id: 'FileDescr' + ItemsIndex,
						fieldLabel: langs('Комментарий'),
						xtype: 'textfield',
						name: 'FileDescr' + ItemsIndex,
						width: this.commentTextfieldWidth
					}]
				};
			}

			var c = new Ext.Panel(
				{
					id: 'Uploader' + ItemsIndex,
					layout: 'column',
					height: 35,
					border: false,	
					defaults: {
						border: false,
						bodyStyle: 'background: transparent; padding-top: 5px'
					},
					items: [fileup, descr, {
						layout: 'form',
						columnWidth: .05,
						items:[{
							handler: function() {
								this.deleteCombo(ItemsIndex)
							}.createDelegate(this),
							xtype: 'tbbutton',
							iconCls: 'delete16',
							tooltip: langs('Удалить')
						}]
					}]
				}
			);
			var cb = this.add(c);
			this.doLayout();
			this.syncSize();
			//this.buttons[0].disable();
			if(this.win)
				this.win.syncSize();
			return cb;
		},
		initComponent: function()
		{
			var cur = this;
			var conf_add_btn = {
				handler: function() {
					this.addCombo();
					this.checkLimitCountCombo();
				}.createDelegate(this),
				iconCls: 'add16',
				disabled:true,
				text: langs('Добавить файл')
			};
			
			if (this.buttonLeftMargin) {
				conf_add_btn.style = 'margin-left: '+ this.buttonLeftMargin +'px;';
			}
			
			/*if(this.firstTabIndex) {
				conf_add_btn.tabIndex = this.firstTabIndex + 11;
			}*/
			this.buttons = [conf_add_btn];
			/*if(typeof this.win != 'object')
				this.win = false;
			if(typeof this.loadParams != 'object')
				this.loadParams = {};
			if(typeof this.baseParams != 'object')
				this.baseParams = {level:0};*/

			sw.Promed.FileUploadPanel.superclass.initComponent.apply(this, arguments);
		}		
	}
);
Ext.reg('swfileuploadpanel', sw.Promed.FileUploadPanel);

/**
* Панель для множественного добавления полей
*
* @package      libs
* @access       public
* @autor		Salakhov R.
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      12.09.2012
*/
sw.Promed.swMultiFieldPanel = Ext.extend(Ext.Panel, {
	layout: 'form',
	label: '',
	deleteBtnText: '',
	firstColWidth: 'auto',
	panelFiledName: 'Field',
	hiddenDelAll: false,
	labelOnAllFields: false,
	frame: true,
	autoHeight: true,
	counter: 0, //счетчик для нумерации полей			
	createField: function(counter) { //создание поля, в этой функции задается тип поля
		var field = new Ext.form.TextField({});
		return field;
	},
	addField: function(value) { //добавляет новое поле в панель, устанавливае значение поля равное value
		var panel = this;
		var isEmpty = false;
		if(this.items.length>0) {
			panel.items.each(function (item, index, length) {
				var c = panel.getField(item);
				if (Ext.isEmpty(c.getValue())) {
					Ext.Msg.alert('Ошибка!','Имеются незаполненные поля!');
					isEmpty = true;
				}
			});
		}
		if(isEmpty)
			return false;
		var first = (panel.counter == 0);
		var c = panel.createField(panel.counter);

		c.name = panel.panelFiledName+panel.counter;
		c.value = value;
		
		if (first && panel.label != '') {
			c.fieldLabel = panel.label;
			c.labelSeparator = ':';
		} else {
			if(!this.labelOnAllFields){
				c.fieldLabel = '';
				c.labelSeparator = '';
			}
		}
		
		var p = new Ext.Panel({		
			height: 28,
			style: 'padding-top: 2px;',
			layout: 'column',
			number: panel.counter,
			first: first,
			items: [{
				width: panel.firstColWidth,
				layout: 'form',
				items: [c]
			}, {
				layout: 'form',
				items: [{
					xtype: 'button',
					style: 'margin-left: 5px;',
					handler: function() {
						if (panel.items.length > 1) {
							var data = new Object();
							var field = panel.getField(this.ownerCt.ownerCt);
							data.name = field.name;
							data.value = field.getValue();									
							
							var item = this.ownerCt.ownerCt;
							if (item) {
								panel.remove(item,true);
								if (item.first)
									panel.setFirstField();
							}
							
							panel.onFieldDelete(data);
						} else {
							panel.getField(this.ownerCt.ownerCt).setValue(null);
						}
					},
					iconCls: 'delete16',
					text: panel.deleteBtnText
				}]
			}]
		});
		panel.add(p);				
		panel.doLayout();
		panel.syncSize();
		panel.counter++;
		panel.onFieldAdd(c);
	},
	onFieldAdd: Ext.emptyFn, //функция вызываемая после добавления нового поля, получает в качестве параметра ссылку на новое поле
	onFieldDelete: Ext.emptyFn, //функция вызываемая после удаления поля, получает в качестве параметра имя и значени удаленного поля, не вызывается при сбросе комбо!
	onResetPanel: Ext.emptyFn, //функция вызываемая после очистки панели
	getField: function(item) {
		var panel = this;
		if (item && item.number != null) {
			var c = item.find('name',panel.panelFiledName+item.number)
			return c[0];
		}
	},
	setFirstField: function() {
		var panel = this;
		if (panel.items.getCount() > 0) {					
			panel.items.get(0).first = true;
			if (panel.label != '') { //устанавливаем у нового "первого" элемента label
				var c = panel.getField(panel.items.get(0));
				//в новых версиях extjs это делается намного проще
				var el = c.el.dom.parentNode.parentNode;
				if( el.children[0].tagName.toLowerCase() === 'label' ) {
					el.children[0].innerHTML = panel.label+':';
				} else if ( el.parentNode.children[0].tagName.toLowerCase() === 'label' ) {
					el.parentNode.children[0].innerHTML = panel.label;
				}
			}
		}
	},
	getData: function(return_type) { //возвращает массив имен + значений полей
		if (!return_type)
			return_type = 'array';
		var panel = this;
		var res_arr = new Array();
		var res_obj = new Object();
		panel.items.each(function(item,index,length) {
			var c = panel.getField(item);
			if (return_type == 'array')
				res_arr.push({name: c.name, value: c.getValue()});
			if (return_type == 'object')
				res_obj[c.name] = c.getValue();
		});
		return return_type == 'array' ? res_arr : res_obj;
	},
	setData: function(arr) { //очищает панель, и создает новые поля со значениями переданными во входящем массиве
		var panel = this;
		panel.reset(false);
		for(var i = 0; i < arr.length; i++) {
			panel.addField(arr[i]);
		}
	},
	reset: function(add_empty_field) { //очищает панель, по умолчанию добавляет пустое поле
		var panel = this;
		panel.items.each(function(item,index,length) {
			panel.remove(item,true);
		});
		panel.counter = 0;
		if (add_empty_field == null || add_empty_field)
			panel.addField();
	},
	disable: function() {
		var panel = this;
		panel.items.each(function(item,index,length) {
			panel.getField(item).disable();
			var btn = item.find('xtype', 'button');
		});
		var btn = panel.find('xtype', 'button');				
		for(var i=0; i < btn.length; i++) {
			btn[i].disable();
		}
		panel.buttons[0].disable();
		panel.buttons[1].disable();
	},
	getCountFields: function(){
		var panel = this;
		return panel.items.length;
	},
	enable: function() {
		var panel = this;
		panel.items.each(function(item,index,length) {
			panel.getField(item).enable();
		});
		var btn = panel.find('xtype', 'button');				
		for(var i=0; i < btn.length; i++) {
			btn[i].enable();
		}
		panel.buttons[0].enable();
		panel.buttons[1].enable();
	},
	initComponent: function() {
		sw.Promed.swMultiFieldPanel.superclass.initComponent.apply(this, arguments);
	},
	buttons: [{
		style: 'margin-left: 97px;',
		handler: function() {
			this.ownerCt.addField();
		},
		iconCls: 'add16',
		text: langs('Добавить')
	}, {
		handler: function() {					
			this.ownerCt.reset();
			this.ownerCt.onResetPanel();
		},
		iconCls: 'delete16',
		text: langs('Удалить все')
	}, {
		text: '-'
	}]
});
Ext.reg('swmultifieldpanel', sw.Promed.swMultiFieldPanel);

/**
 * Панель для множественного добавления полей
 * другая версия )
 *
 * @package      libs
 * @access       public
 * @autor		 m.sysolin
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @version      26.01.2018
 */
sw.Promed.swDuplicatedFieldPanel = Ext.extend(Ext.Panel, {
	layout: 'border',
	region: 'center',
	fieldLbl: 'Название поля', // переопределяется
	fieldName: 'DuplicatedFieldName',  // переопределяется
	dynamicWndHeight: null,
	dynamicPanelHeight: null,
	fieldConfig: null,
	labelWidth: 120,
	fieldWidth: 100,
	height: 46,
	hideAddBtn: false,
	allBtnsDisabled: false,
	onlyUniq: true, // возврат только уникальных значений
	viewMode: false, // режим просмотра
	fullScreenWnd: false,
	changeFieldWidth: function(reset) {

		var panel = this,
			ownerWnd = panel.ownerWindow,
			fieldsContainer = panel.findById(panel.id + '_' + 'itemsContainer');

		var field = fieldsContainer.getComponent(0).getComponent(0);

		if (field) {
			field.setWidth((!reset) ? panel.fieldWidth * 2 : panel.fieldWidth);
			field.syncSize();
		}
	},
	fillPanelByData: function(params) {

		var panel = this;
		if (typeof params != 'object') params = new Object();

		if (params.panelValues) {

			var valuesList = params.panelValues.split(', ');
			if (valuesList) {

				if (params.fillInLine) {

					valuesList = valuesList.toString().replace(/,+$/,'');

					panel.setValueByIndex(0, valuesList);
					log('valuesList.length', valuesList.length);
					if (valuesList.length > 10) {
						panel.changeFieldWidth();
					} else {
						panel.changeFieldWidth(true);
					}

				} else {

					valuesList.forEach(function(tth, k){

						var val = parseInt(tth.trim());

						if (k > 0) { panel.addField() }
						panel.setValueByIndex(k, val);
					});
				}

				if (panel.viewMode) panel.disableAll();
			}
		}
	},
	getData: function(data) {

		var panel = this,
			fieldsData = [];

		var	fieldsContainer = panel.findById(panel.id + '_' + 'itemsContainer');
		var	items = fieldsContainer.items;

		if (items.length > 0) {

			items.each(function(field) {
				var toothNum = field.getComponent(0).getValue();
				if (toothNum) fieldsData.push(toothNum);
			});
		}

		var outputData = fieldsData;
		if (panel.onlyUniq) {

			outputData = fieldsData.filter(
				function(val, i, arr) {
					return arr.indexOf(val) === i;
				}
			);
		}

		return outputData;
	},
	setValueByIndex: function(index,value){

		var panel = this,
			ownerWnd = panel.ownerWindow,
			fieldsContainer = panel.findById(panel.id + '_' + 'itemsContainer');

		fieldsContainer.getComponent(index).getComponent(0).setValue(value);
	},
	disableAll: function(){

		var panel = this,
			ownerWnd = panel.ownerWindow,
			fieldsContainer = panel.findById(panel.id + '_' + 'itemsContainer');

		var items = fieldsContainer.items;
		panel.allBtnsDisabled = true; // чтобы триггер не сработал
		panel.getComponent(1).hide(); // скрываем кнопку добавить

		panel.setHeight(panel.dynamicPanelHeight - 20);
		panel.syncSize(); panel.doLayout();

		if (items.length > 0) {

			items.each(function(triggerFieldComponent) {
				var fld = triggerFieldComponent.getComponent(0);
				fld.setDisabled(true);
			});
		}
	},
	clearPanel: function() {

		var panel = this,
			ownerWnd = panel.ownerWindow,
			fieldsContainer = panel.findById(panel.id + '_' + 'itemsContainer');

		var items = fieldsContainer.items;
		panel.dynamicWndHeight = ownerWnd.height;
		panel.dynamicPanelHeight = panel.height;

		// убираем лишние поля с формы
		if (items.length > 1) {

			items.each(function(triggerFieldComponent, key) {
				if (key > 0) { fieldsContainer.remove(triggerFieldComponent) }
			});

			// изменяем высоту
			panel.setHeight(panel.height);
			if (!panel.fullScreenWnd) ownerWnd.setHeight(ownerWnd.height);

			ownerWnd.doLayout(); ownerWnd.syncSize();
		}

		// очистим первое поле
		fieldsContainer.getComponent(0).getComponent(0).setValue();
	},
	addField: function() {

		var panel = this,
			ownerWnd = panel.ownerWindow;

		var btn = Ext.get(panel.id + '_' + 'addFieldLink'),
			fieldsContainer = panel.findById(panel.id + '_' + 'itemsContainer');

		//if (btn.hasClass('hyperlink-disabled-grey')) {log('already disabled')}
		//else btn.addClass('hyperlink-disabled-grey');

		//return false;

		//if (is_first) wnd.lastItemsIndex = 0; else wnd.lastItemsIndex++;
		//
		//if (wnd.lastItemsIndex > wnd.limitCountCombo) {
		//
		//	if (wnd.buttons && wnd.buttons[0]) wnd.buttons[0].setDisabled(true);
		//	return false;
		//
		//} else {
		//	if (wnd.buttons && wnd.buttons[0]) wnd.buttons[0].setDisabled(false);
		//}

		// if (wnd.firstTabIndex) { fieldConfig.tabIndex = wnd.firstTabIndex + wnd.lastItemsIndex }

		// добавляем поле
		fieldsContainer.add(panel.returnFieldComponent());

		// изменяем высоту
		panel.setHeight(panel.dynamicPanelHeight += 26);
		if (!panel.fullScreenWnd) ownerWnd.setHeight(panel.dynamicWndHeight += 26);

		ownerWnd.doLayout(); ownerWnd.syncSize();
		//return addFieldResult;
	},
	removeField: function(fieldId){

		var panel = this;
		var ownerWnd = panel.ownerWindow;

		var triggerField = panel.findById(fieldId),
			fieldsContainer = panel.findById(panel.id + '_' + 'itemsContainer');

		var items = parseInt(fieldsContainer.items.length);

		if (triggerField) {

			var triggerFieldComponent = triggerField.ownerCt;

			// очищаем поле
			triggerField.setValue();

			// удаляем поле, если оно не единственное
			if (items > 1) {

				// удаляем из Ext
				fieldsContainer.remove(triggerFieldComponent);

				// изменяем высоту
				panel.setHeight(panel.dynamicPanelHeight -= 26);
				if (!panel.fullScreenWnd) ownerWnd.setHeight(panel.dynamicWndHeight -= 26);

				ownerWnd.doLayout(); ownerWnd.syncSize();
			}
		}
	},
	returnFieldComponent: function() {
		return {
			xtype: 'panel',
			layout: 'form',
			border: false,
			labelWidth: this.labelWidth,
			items: [this.getFieldConfig()]
		};
	},
	getFieldConfig: function() {

		var panel = this;
		var ownerWnd = panel.ownerWindow;
		var fieldId = Ext.id();

		var fName =  panel.fieldName + '_' + fieldId;

		var defaultConfig = {
			forceSelection: true,
			fieldLabel: panel.fieldLbl,
			name: fName,
			id: fieldId,
			typeAhead: false,
			triggerClass: 'x-form-clear-trigger',
			autoCreate: {tag: "input",  maxLength: "2", autocomplete: "off"},
			width: panel.fieldWidth,
			maskRe: /[0-9]/,
			onTriggerClick: function() { if (!panel.allBtnsDisabled) panel.removeField(fieldId) }
		}

		if (panel.fieldConfig) {
			panel.fieldConfig.id = fieldId,
			panel.fieldConfig.name = fName,
			panel.fieldConfig.fieldLabel = panel.fieldLbl
		}

		return (panel.fieldConfig ? panel.fieldConfig : (new Ext.form.TriggerField(defaultConfig)));
	},
	initComponent: function() {

		var panel = this;
		panel.dynamicWndHeight = panel.ownerWindow.height;
		panel.dynamicPanelHeight = panel.height;

		Ext.apply(this,	{
			items: [
				{
					xtype: 'container',
					border: false,
					id: panel.id + '_' + 'itemsContainer',
					region: 'center',
					layout: 'form',
					autoEl: {},
					items:[panel.returnFieldComponent()]
				},{
					xtype: 'panel',
					layout: 'form',
					region: 'south',
					hidden: (panel.hideAddBtn || panel.viewMode),
					height: 20,
					items: [
						{
							html: "<a href='#' id='" + panel.id+ "_addFieldLink' onclick='Ext.getCmp(\"" + panel.id + "\").addField()'>Добавить</a>",
							id: panel.id + '_' + "addFieldLinkPanel",
							xtype: "panel",
							style: 'padding-left: ' + (panel.labelWidth + 5) + 'px;',
						}
					]
				}
			]
		});
		sw.Promed.swDuplicatedFieldPanel.superclass.initComponent.apply(this, arguments);
	},
});
Ext.reg('swduplicatedfieldpanel', sw.Promed.swDuplicatedFieldPanel);

/**
* Панель комбобоксов лек.средств с возможностью выбора ввода по торг.наименованию или по МНН
*
* @package      libs
* @access       public
* @autor		Alexander Permyakov
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      22.03.2012
*/
sw.Promed.swDrugPanel = Ext.extend(Ext.Panel,
{
	win: null,
	form: null,
	onSelectDrug: null,
	onClearDrug: null,
	LpuSection_id: null,
	defaultMethodInputDrug_id: 1,
	firstTabIndex: null,
    selectedDrug: null,
	
	autoHeight: true,
	labelAlign: 'right',
	layout: 'form',
	labelWidth: 120,
	bodyStyle: 'padding: 3px;',
	border: false,
	defaults: {
		border: false
	},
	findField: function(name) {
		var r = this.find('hiddenName',name);
		return (r && r[0])?r[0]:false;
	},
	getFirstField: function() {
		return this.findField('MethodInputDrug_id');
	},
	setFieldVisible: function(name,flag,allow_clear) {
		var f = this.findField(name);
		if(!f) return false;
		if(allow_clear) {
			f.setValue(null);
			f.setRawValue('');
		}
		f.setAllowBlank(!flag);
		f.setContainerVisible(flag);
		return true;
	},
	onSelectMethodInputDrug: function(value,allow_clear) {
		this.setFieldVisible('DrugComplexMnn_id',(value == 1),allow_clear);
		//this.setFieldVisible('DrugPrepFas_id',(value != 1),allow_clear);
		this.setFieldVisible('Drug_id',(value != 1),allow_clear);
		this.doLayout();
		this.syncSize();
		if(this.win) {
			var form;
			if(this.win.FormPanel) {
				form = this.win.FormPanel;
			} else if(this.form_id) {
				form = this.win.findById(this.form_id);
			} 
			if(form) {
				form.doLayout();
				form.syncSize();
			}
			this.win.doLayout();
			this.win.syncSize();
		}
	},
	reset: function() {
		this.LpuSection_id = null;
		var f = this.findField('MethodInputDrug_id');
		if(!f) return false;
		f.setValue(this.defaultMethodInputDrug_id);
		this.onSelectMethodInputDrug(this.defaultMethodInputDrug_id,true);
	},
	loadCombo: function(combo, params, callback) {
		combo.getStore().load({
			callback: function() {
				if ( combo.getStore().getCount() > 0 && combo.getValue() > 0 ) {
					combo.setValue(combo.getValue());
                    if (typeof callback == 'function') {
                        var rec = combo.getStore().getById(combo.getValue());
                        callback(rec);
                    }
				}
			},
			params: params
		});
	},
	onLoadForm: function() {
		var thas = this,
            f = this.findField('MethodInputDrug_id'),
			f2 = this.findField('DrugComplexMnn_id'),
			//f3 = this.findField('DrugPrepFas_id'),
			f4 = this.findField('Drug_id');
		if(!f) return false;
		this.onSelectMethodInputDrug(f.getValue(),false);
        var baseParams = {
            LpuSection_id: this.LpuSection_id,
            isFromDocumentUcOst: 'off'
        };
        if ( this.win && this.win.parentEvnClass_SysNick && this.win.parentEvnClass_SysNick == 'EvnSection' ) {
            baseParams.isFromDocumentUcOst = 'on';
        }
		f2.getStore().baseParams = baseParams;
		f4.getStore().baseParams = baseParams;
		if(f.getValue() == 1) {
			if(!f2) return false;
			this.loadCombo(f2, {
                DrugComplexMnn_id: f2.getValue()
            }, function (rec) {
                if (rec) {
                    f2.fireEvent('select', f2, rec);
                }
            });
		} else {
			if(!f4) return false;
			this.loadCombo(f4, {
                Drug_id: f4.getValue()
            }, function (rec) {
                if (rec) {
                    f4.fireEvent('select', f4, rec);
                }
            });
			/*if(!f3 || !f4) return false;
			this.loadCombo(f3,{'DrugPrepFas_id': f3.getValue()});
			this.loadCombo(f4,{'DrugPrepFas_id': f3.getValue()});*/
		}
	},
	initComponent: function()
	{
		// Выбор ввода по торг.наименованию или по МНН
		this.conf_swmethodinputdrugcombo = {
			allowBlank: false,
			hiddenName: 'MethodInputDrug_id',
			fieldLabel: langs('Назначение'),
			value: this.defaultMethodInputDrug_id,
			anchor: '96%',
			listeners: {
				'select': function(combo, record) {
					this.onSelectMethodInputDrug(combo.getValue(),true);
				}.createDelegate(this)
			},
			xtype: 'swmethodinputdrugcombo'
		};
		if(this.firstTabIndex) {
			this.conf_swmethodinputdrugcombo.tabIndex = this.firstTabIndex;
		}
		
		// МНН
		this.conf_swdrugcomplexmnncombo = {
			hiddenName: 'DrugComplexMnn_id',
			anchor: '96%',
			listeners: {
				'select': function(combo, record) {
                    this.selectedDrug = record;
					if(typeof this.onSelectDrug == 'function') {
						this.onSelectDrug(combo, record);
					}
				}.createDelegate(this)
			},
			xtype: 'swdrugcomplexmnncombo'
		};
		if(this.firstTabIndex) {
			this.conf_swdrugcomplexmnncombo.tabIndex = this.firstTabIndex + 1;
		}
		if(typeof this.onClearDrug == 'function') {
			this.conf_swdrugcomplexmnncombo.onClearValue = this.onClearDrug;
		}
		// Медикамент
		this.conf_swdrugsimplecombo = {
			hiddenName: 'Drug_id',
			anchor: '96%',
			listeners: {
				'select': function(combo, record) {
                    this.selectedDrug = record;
					if(typeof this.onSelectDrug == 'function') {
						this.onSelectDrug(combo, record);
					}
				}.createDelegate(this)
			},
			xtype: 'swdrugsimplecombo'
		};
		if(this.firstTabIndex) {
			this.conf_swdrugsimplecombo.tabIndex = this.firstTabIndex + 2;
		}
		if(typeof this.onClearDrug == 'function') {
			this.conf_swdrugsimplecombo.onClearValue = this.onClearDrug;
		}
		/*
		// Медикамент
		this.conf_swdrugprepcombo = {
			hiddenName: 'DrugPrepFas_id',
			anchor: '96%',
			listeners: {
				'select': function(combo, record) {
					if(typeof this.onSelectDrug == 'function') {
						this.onSelectDrug(combo, record);
					}
				}.createDelegate(this)
			},
			xtype: 'swdrugprepcombo'
		};
		if(this.firstTabIndex) {
			this.conf_swdrugprepcombo.tabIndex = this.firstTabIndex + 2;
		}

		// Упаковка
		this.conf_swdrugpackcombo = {
			hiddenName: 'Drug_id',
			anchor: '96%',
			xtype: 'swdrugpackcombo'
		};
		if(this.firstTabIndex) {
			this.conf_swdrugpackcombo.tabIndex = this.firstTabIndex + 3;
		}
		*/
		Ext.apply(this,	{
			items: [
				this.conf_swmethodinputdrugcombo,
				this.conf_swdrugcomplexmnncombo,
				this.conf_swdrugsimplecombo
				/*this.conf_swdrugprepcombo,
				this.conf_swdrugpackcombo*/
			]
		});
		sw.Promed.swDrugPanel.superclass.initComponent.apply(this, arguments);
	}
});
/**
* Панель карты GoogleMaps
*
* @package      libs
* @access       public
* @autor		Alexandr Miyusov
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      24.09.2012
*/
sw.Promed.GoogleMapPanel = Ext.extend(Ext.Panel, {
	iconVan: '/img/googlemap/van.png',
	iconFirstaid: '/img/googlemap/firstaid.png',

	initComponent : function(){
		if(!(window.google || {}).maps){
			Ext.Msg.alert('Google Maps API is required');
		}
		
		var defPOVConfig = {
			heading: 34,
			pitch: 10,
			zoom: 1
		};

		Ext.applyIf(this,defPOVConfig);

		sw.Promed.GoogleMapPanel.superclass.initComponent.call(this);        
	},
	afterRender : function(){      
		var wh = this.ownerCt.getSize();
		Ext.applyIf(this, wh);

		sw.Promed.GoogleMapPanel.superclass.afterRender.call(this);    
		this.renderMap();
	},
	renderMap : function(){
		this.mapOptions = this.mapOptions || {};
		Ext.applyIf(this.mapOptions, {
			//center: new google.maps.LatLng(58.0138889,56.2488889),//Россия, Пермь
			zoom: 11,
			scaleControl: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP // HYBRID, ROADMAP, SATELLITE, TERRAIN
		});
		
		this.directionsService = new google.maps.DirectionsService();
		this.directionsDisplay = new google.maps.DirectionsRenderer({
			suppressMarkers:true
		});
		this.googleMarkers = new Array();
		this.gmap = new google.maps.Map(this.body.dom, this.mapOptions);
		this.directionsDisplay.setMap(this.gmap);
		if (this.addMarkByClick) {
			google.maps.event.addListener(this.gmap, 'click', this.doClick.createDelegate(this));
		}
		
		if (typeof this.setCenter === 'object') {
			if (typeof this.setCenter.geoCodeAddr === 'string') {
				this.geoCodeLookup(this.setCenter.geoCodeAddr);
				// geoCodeLookkup is async thus the onMapReady is called after successful respond.
			} else {
				var point = new google.maps.LatLng(this.setCenter.lat,this.setCenter.lng);
				this.gmap.setCenter(point);    
				if (typeof this.setCenter.marker === 'object' && typeof point === 'object'){
					var iwo = (this.setCenter.infoWindowOptions) ? this.setCenter.infoWindowOptions : null;
					this.centerMarker = this.addMarker(this.getCenter(),this.setCenter.marker,this.setCenter.marker.clear, true, this.setCenter.listeners, iwo);
				}		
			}
		}
		this.addMarkers(this.markers);
		this.fireEvent('maprender', this, this.gmap);
	},
	doClick: function (event) {
		if (this.addMarkByClick) {
			if (typeof this.currentMarker === 'object') {
				this.currentMarker.setMap(null);
			}
			this.currentMarker = this.addMarker(event.latLng,{title:langs('точка, которую некуда пока сохранить')},false,false,false,null,this.iconFirstaid);
			this.defineClosestRoute();
			if (typeof this.fillLatLng != 'undefined' && this.fillLatLng) {
				if (typeof this.latField.setValue == 'function') {
					this.latField.setValue(this.currentMarker.getPosition().lat());
				}
				if (typeof this.lngField.setValue == 'function') {
					this.lngField.setValue(this.currentMarker.getPosition().lng());
				}
			}
		}
		if (typeof this.clickCallback == 'function') {
			this.clickCallback(event);
		}
		
	},
	setCurrentMarkerLatLng: function(lat,lng) {
		if (typeof this.currentMarker === 'object') {
			this.currentMarker.setMap(null);
		}
		var markerPoint = new google.maps.LatLng(lat,lng);
		this.currentMarker = this.addMarker(markerPoint,{title:langs('точка, которую некуда пока сохранить')},false,false,false,null,this.iconFirstaid);
		this.gmap.setCenter(markerPoint);
		this.defineClosestRoute();
		if (typeof this.fillLatLng != 'undefined' && this.fillLatLng) {
			if (typeof this.latField.setValue == 'function') {
				this.latField.setValue(this.currentMarker.getPosition().lat());
			}
			if (typeof this.lngField.setValue == 'function') {
				this.lngField.setValue(this.currentMarker.getPosition().lng());
			}
		}
	},
	onResize : function(w, h){
		sw.Promed.GoogleMapPanel.superclass.onResize.call(this, w, h);
	},
	setSize : function(width, height, animate){
		sw.Promed.GoogleMapPanel.superclass.setSize.call(this, width, height, animate);
	},
	getMap : function(){        
		return this.gmap;       
	},
	getCenter : function(){    
		return this.getMap().getCenter();  
	},
	setCenter: function(point) {
		if (typeof point[0] == 'undefined' || typeof point[1] == 'undefined' ) {
			return false;
		}
		gPoint = new google.maps.LatLng(point[0],point[1]);
		this.gmap.setCenter(gPoint);
	},
	getCenter : function(){    
		return this.getMap().getCenter();  
	},
	getCenterLatLng : function(){
		var ll = this.getCenter();
		return {lat: ll.lat(), lng: ll.lng()};
	},
	getState : function(){
		return this.mapOptions;  
	},
	addMarkers : function(markers) {
		if (Ext.isArray(markers)){
			for (var i = 0; i < markers.length; i++) {
				var mkr_point = new google.maps.LatLng(markers[i].lat,markers[i].lng);
				var iwo = (markers[i].infoWindowOptions) ? markers[i].infoWindowOptions : null;
				var icon = markers[i].icon ? markers[i].icon : this.iconVan;
			
				this.googleMarkers.push(this.addMarker(mkr_point,markers[i].marker,false,markers[i].setCenter, markers[i].listeners, iwo, icon, markers[i].additionalInfo));
			}
		}
	},
	addMarker : function(point, marker, clear, center, listeners, infoWindowOptions,iconDirection, addInfo){
		if (clear === true){
			this.getMap().clearOverlays();
		}
		if (center === true) {
			this.getMap().setCenter(point);
		}

		var mark = new google.maps.Marker({
			map: this.getMap(),
			position: point,
			title: marker.title,
			icon: iconDirection
		});
		
		if (typeof addInfo === 'object'){
			for (key in addInfo) {
				if (addInfo.hasOwnProperty(key)) {
					mark[key] = addInfo[key];
				}
			}
		}
		
		var infoWindow = null;
		if (infoWindowOptions != null) {
			infoWindowOptions.content = infoWindowOptions.content + 
				'<br/><button onClick=\'Ext.Msg.alert(\"Внимание\", \"Функционал в разработке.\");\'> Подробная информация о бригаде </button>'
			infoWindow = new google.maps.InfoWindow(infoWindowOptions);
			google.maps.event.addListener(mark, 'click', this.onMarkClick.createDelegate(this, [mark,infoWindow]));
		}
		
		
		
		if (typeof listeners === 'object') {
			for (evt in listeners) {
				if (listeners.hasOwnProperty(evt)) {
					google.maps.event.addListener(mark, evt, listeners[evt]);
				}
			}
		}
		return mark;
	},
	
	defineClosestRoute: function() {
		var minDistance = 10000000;
		var closestMark = null;
		var distance;
		for (var i=0; i<this.googleMarkers.length; i++) {
			distance = google.maps.geometry.spherical.computeDistanceBetween(
				this.currentMarker.getPosition(),
				this.googleMarkers[i].getPosition()
			);
			if (distance<minDistance) {
				minDistance = distance;
				closestMark = this.googleMarkers[i];
			}
		}
		
		this.onMarkClick(closestMark);
	},
	
	onMarkClick: function(mark,infoWindow) {
		if (!mark||typeof mark == 'undefined') return false;
		var directDisp = this.directionsDisplay;
		var start = mark.getPosition();
		if (!this.currentMarker||typeof this.currentMarker == 'undefined') return false;
		var end = this.currentMarker.getPosition();
		
		var statuspanel = this.ownerCt.findById('gmap_status_panel'),
		statusfield = statuspanel.find('name', 'gmap_status_field')[0];
		var map = this.gmap;
		var request = {
			origin:start,
			destination:end,
			travelMode: google.maps.TravelMode.DRIVING
		};
		
		if (typeof this.infoWindow != 'undefined'){
			this.infoWindow.close();
		}
		
		this.directionsService.route(request, function(result, status) {
			if (status == google.maps.DirectionsStatus.OK) {
			directDisp.setDirections(result);
			statusfield.getEl().update('<div style="margin-left: 5px; width:500px; height: 16px;">Расстояние до места: '+
				result.routes[0].legs[0].distance.text+'; Ожидаемое время прибытия:'+ result.routes[0].legs[0].duration.text + '</div>');
			log(statuspanel);
			statuspanel.setVisible(true);
			//log(result); routes[0].legs[0].distance.text; routes[0].legs[0].duration.text
			}
			if (typeof infoWindow != 'undefined') {
				infoWindow.open(map,mark);
			}
		});
		this.infoWindow = infoWindow;
	},
	// @private
	geoCodeLookup : function(addr) {
		this.geocoder = new google.maps.Geocoder();
		this.geocoder.geocode({'address': addr}, this.addAddressToMap.createDelegate(this));        
	},
	// @private
	addAddressToMap : function(results, status) {
		if (status !== google.maps.GeocoderStatus.OK) {
			Ext.MessageBox.alert('Error', 'Code '+response.Status.code+' Error Returned');
		}else{
			var place = results[0],
			addressinfo = place.formatted_address,
			point = place.geometry.location;
			if (typeof this.setCenter.marker === 'object' && typeof point === 'object'){
				if (typeof this.currentMarker === 'object')
					this.currentMarker.setMap(null);
				this.currentMarker = this.addMarker(point,this.setCenter.marker,this.setCenter.marker.clear,true, this.setCenter.listeners,null,this.iconFirstaid);
			}

			this.getMap().setCenter(point);
			this.defineClosestRoute();			
		}        
	},
	onDestroy : function() {
		if (this.map && (window.google || {}).maps) {
			google.maps.event.clearInstanceListeners(this.map);
		}
		sw.Promed.GoogleMapPanel.superclass.onDestroy.call(this);
	},
	onUpdate : function(map, e, options) {
		this.update((options || {}).data);
	}, 
	// currently it only pan to the new coordinate
	// for future extension
	update : function(coordinates) {
		coordinates = coordinates || new google.maps.LatLng(58.0138889,56.2488889);//Россия, Пермь

		if (coordinates && !(coordinates instanceof google.maps.LatLng) && 'lng' in coordinates) {
			coordinates = new google.maps.LatLng(coordinates.lat, coordinates.lng);
		}

		if (!this.hidden && this.rendered) {
			this.gmap || this.renderMap();
			if (this.gmap && coordinates instanceof google.maps.LatLng) {
				this.getMap().setCenter(coordinates);
			this.gmap.panTo(coordinates);
			}
		}
		else {
			this.on('activate', this.onUpdate, this, {single: true, data: coordinates});
		}
	},    
	// @private
	onResize : function( w, h) {
		sw.Promed.GoogleMapPanel.superclass.onResize.apply(this, arguments);
		if (this.gmap) {
			google.maps.event.trigger(this.gmap, 'resize');
		}
	}    
	// for future extension
		, useCurrentLocation: false
		, gmap: null
});

Ext.reg('gmappanel', sw.Promed.GoogleMapPanel); 

/**
 * Панель Wialon
 */
sw.Promed.WialonPanel = Ext.extend(Ext.Panel,{
	
	title: 'Wialon',
	
	city: 'Пермь',
	
	mapWidth: '100%',
	
	mapHeight: '100%',
	
	autoWidth: false,
	
	autoHeight: false,
	
	width: 500,
	
	height: 500,
	
	layout: 'fit',
	
	refreshTime: 20000,
	
	/**
	 * @private
	 */
	map: null,
	
	/**
	 * @private
	 */
	geocoder: null,
	
	directionsDisplay: null,
	
	directionsService: null,
	
	/**
	 * @var Размер иконки объекта
	 */
	iconMaxBorder: 32,
	
	/**
	 * @var Объекты на карте
	 */
	units: [],
	
	/**
	 * @var Кеш IDs объектов
	 */
	cacheUnitsByEmergencyTeamId: {},
	
	/**
	 * @var Маркер места вызова
	 */
	markerEmergencyCall: null,
	
	// Список марекеров с адресами на карте
//	addressMarkers: [],
	
    /**
     * @cfg boolean showObjectsOnMap Показывать объекты на карте
     */
//	showUnitsOnMap: false,
	
	/**
	 * @var Units list
	 */
//	unitsOnMap: [],
	
	/**
	 * @var Units makers list
	 */
//	unitsMarkers: {},
		
	loadScript: function( src, callback ) {
		var script = document.createElement('script');
		var appendTo = document.getElementsByTagName('head')[0];
		// Callback
		if ( script.readyState && !script.onload ) {
			// IE, Opera
			script.onreadystatechange = function(){
				if ( script.readyState == "loaded" || script.readyState == "complete" ) {
					script.onreadystatechange = null;
					if ( typeof callback == 'function' ) {
						callback();
					}
				}
			}
		} else if ( typeof callback == 'function' ) {
			// Rest
			script.onload = callback;
		}
		script.src = src;
		appendTo.appendChild( script );
	},
	
	getXmlHttp: function(){
		var xmlhttp;
		try {
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (E) {
				xmlhttp = false;
			}
		}
		if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
			xmlhttp = new XMLHttpRequest();
		}
		return xmlhttp;
	},
	
	request: function(options, callback){
		var params = Ext.apply({
			method: 'POST',
			url: null,
			async: false,
			type: 'html',
			success: null,
			failure: null
		},options);
		
		Ext.Ajax.request({
			url: params.url,
			method: params.method,
			success: function(response, opts) {
				if ( (response.status==200) && (response.success!=false))
				{
					var res = JSON.parse(response.responseText);
					callback(res);
				}
				
			},
			failure: function(response, opts) {
				//Ext.MessageBox.alert('Ошибка', 'Обратитесь к администратору');
			}
		});

	},
	
	getMap: function(){
		return this.map;
	},
	
	getDirectionsDisplay: function(){
		if ( this.directionsDisplay === null ) {
			this.directionsDisplay = new google.maps.DirectionsRenderer();
		}
		return this.directionsDisplay;
	},
	
	getDirectionsService : function(){
		if ( this.directionsService === null ) {
			this.directionsService = new google.maps.DirectionsService();
		}
		return this.directionsService;
	},
	
	calcRoutBetweenTwoMarkers: function(start,end){
//		var directionsDisplay = new google.maps.DirectionsRenderer();
//		directionsDisplay.setMap( this.getMap() );
//		var directionsService = new google.maps.DirectionsService();
		var request = {
			origin: start,
			destination: end,
			travelMode: google.maps.TravelMode.DRIVING
		};
		this.getDirectionsService().route(request,function(response,status){
			if ( status == google.maps.DirectionsStatus.OK ) {
				this.getDirectionsDisplay().setDirections(response);
			}
		}.createDelegate(this));
	},
	
	// Возвращает список объектов
	getAllAvlUnitsWithCoords: function(callback){
		log('getAllAvlUnitsWithCoords');
		var items = this.request({
			method: 'POST',
			url: '?c=Wialon&m=getAllAvlUnitsWithCoords',
			async: false, // Синхронный запрос возвращающий значение
			type: 'json'
		}, function(res){
			if (callback) callback(res);
		});
		//return items;
		//return items;
	},
	
	// Заполняет карту объектами
	fillMapWithUnits: function(){

		var item;		
		for( var key in this.units ){
			item = this.units[ key ];
			
			if ( typeof item.pos == 'undefined' ) {
				continue;
			}
			
			if ( typeof item.pos.y == 'undefined' || typeof item.pos.x == 'undefined' ) {
				continue;
			}
			
			this.units[ key ].marker = new google.maps.Marker({
				position: new google.maps.LatLng( item.pos.y, item.pos.x ),
				map: this.map,
				title: item.nm,
				icon: {
					url: "/img/icons/ambulance32.png",
					anchor: new google.maps.Point(17, 20)
				}
//				icon: '/?c=Wialon&m=avlItemImage&id='+item.id
//				icon: 'http://195.128.137.36:8022/avl_icon/get/' + item.id + '/' + this.iconMaxBorder + '/1.png'
			});
			
			if (item.pos.c > 0){
				this.units[ key ].arrow = new google.maps.Marker({
					position: new google.maps.LatLng(item.pos.y, item.pos.x),
					map: this.map,
					icon:{
						path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW, 
						scale:4,
						rotation: item.pos.c,
						fillColor: 'green',
						fillOpacity: 0.8,
						strokeWeight: 1,
						strokeColor: '#e1e1e1',
						anchor: new google.maps.Point(0, 10)
					}
				});
			}

	 	}
		
		//авторефреш
		setInterval(function(){
			this.refreshItems();
		}.createDelegate(this), this.refreshTime);
	},
	
	refreshItems: function(items){
		var me = this;
		
		function doRefresh(result){
			if (!me.units && result){
				me.units = result.items;
				me.fillMapWithUnits();
			}
			for (var i=0;i<me.units.length;i++) {
				var newValObject = result.items.filter(function( obj ) 
					{
						return obj.id == me.units[i].id;
					}.createDelegate(me));
					if (newValObject){
						if (typeof(me.units[i].visible) != 'undefined' ){
							me.units[i].marker.setVisible(me.units[i].visible);
								if (typeof me.units[i].arrow != 'undefined'){
									me.units[i].arrow.setVisible(me.units[i].visible);
								}
						}

						//объект переместился?
						if(
							newValObject[0].pos.y != me.units[i].pos.y ||
							newValObject[0].pos.x != me.units[i].pos.x
						){
							if (me.animateMarkers){
								//позиция машинки
								me.units[i].marker.animateTo(new google.maps.LatLng( newValObject[0].pos.y, newValObject[0].pos.x ), {  
									easing: "linear",
									duration: me.refreshTime,
									complete: function() {
									   //alert("animation complete");
									}
								});
							}
							else{
								me.units[i].marker.setPosition(new google.maps.LatLng( newValObject[0].pos.y, newValObject[0].pos.x ));
							}
							//позиция и поворот курсора направления
							if (typeof me.units[i].arrow != 'undefined'){								
								if (me.animateMarkers){
									me.units[i].arrow.animateTo(new google.maps.LatLng( newValObject[0].pos.y, newValObject[0].pos.x ), {  
										easing: "linear",
										duration: 20000,
										complete: function() {
											//alert("animation complete");
										}
									});
								}
								else{
									me.units[i].arrow.setPosition(new google.maps.LatLng( newValObject[0].pos.y, newValObject[0].pos.x ));
								}
								me.units[i].arrow.setIcon(
									{
										path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW, 
										scale:4, 
										rotation: newValObject[0].pos.c,
										fillColor: 'red',
										fillOpacity: 0.8,
										strokeWeight: 1,
										strokeColor: '#e1e1e1',
										anchor: new google.maps.Point(0, 10)
									}
								)
							}
						}
						else{

						}
					}
				}
				return result.items;
			}
			
		if (items){
			doRefresh(items);
		}
		else{
			log('else');
			this.getAllAvlUnitsWithCoords(function(result){
				log('getcoords', result);
				doRefresh(result);
			}.createDelegate(this));
		}	
	},
	
	setCenterUnit: function( unit_id ){
		var unit;
		
		for( var key in this.units  ) {
			unit = this.units[ key ];
			if ( unit.id == unit_id ) {
				if ( typeof unit.pos != 'undefined' ) {
					this.map.setCenter( unit.marker.position );
				}
				break;
			}
		}
	},
	
	setCenterUnitByEmergencyTeamId: function( EmergencyTeam_id ){
		if ( typeof this.cacheUnitsByEmergencyTeamId[EmergencyTeam_id] == 'undefined' ) {
			var wialon = this;
			// Basic request
			Ext.Ajax.request({
				url: '?c=Wialon&m=getUnitIdByEmergencyTeamId',
				success: function(xmlhttp){
					var data = Ext.util.JSON.decode( xmlhttp.responseText );
					wialon.cacheUnitsByEmergencyTeamId[EmergencyTeam_id] = data[0];
					wialon.setCenterUnit( wialon.cacheUnitsByEmergencyTeamId[EmergencyTeam_id] );
				},
				failure: function(){
					log('Failed to get wialon unit id');
				},
				params: {
					EmergencyTeam_id: EmergencyTeam_id
				}
			});
		} else {
			this.setCenterUnit( this.cacheUnitsByEmergencyTeamId[EmergencyTeam_id] );
		}
	},
	
	initGurtamMaps: function(){
		
		// initialize Google maps, execute after script loaded
		// for additionsl info about Custom map types see link below 
		// https://developers.google.com/maps/documentation/javascript/maptypes#MapTypeInterface
		function GurtamMapsType() {}; // Gurtam map type variable
		GurtamMapsType.prototype.tileSize = new google.maps.Size(256, 256); // specify size of the tile
		// specify maximum zoom level at which to display tiles of Gurtam map
		GurtamMapsType.prototype.maxZoom = 17;
		// specify the name for Gurtam map type
		GurtamMapsType.prototype.name = "Gurtam";
		// specify alternate text for Gurtam map type, exhibited as hover text
		GurtamMapsType.prototype.alt = "Gurtam Maps";
		// getTile() is called when API determines that map needs to display new tiles
		GurtamMapsType.prototype.getTile = function(coord, zoom, ownerDocument) {
			var url = "http://render.mapsviewer.com/hst-api.wialon.com/gis_render/"+
				coord.x + "_" + coord.y + "_" + (this.maxZoom-zoom) + "/tile.png";
			var img = ownerDocument.createElement("IMG"); // create <img> tile element
			img.src = url; // specify source of tile
			img.style.width = this.tileSize.width + "px"; // specify width of tile
			img.style.height = this.tileSize.height + "px"; // specify height of tile
			img.style.border = "0px"; // hide border
			return img; // return tile
		};
		
		var mapOptions = { // map options
			mapTypeId: "gurtammaps", // specify initial map type
			mapTypeControlOptions: { // control options
				// specify list of available map types
				mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE, "gurtammaps"],
				// specify style of control
				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
			}
		}
		
		this.map.setOptions( mapOptions );
		var gurtam = new GurtamMapsType(); // create new instance of GurtamMap
		this.map.mapTypes.set("gurtammaps", gurtam);
		
		this.getDirectionsService();
		this.getDirectionsDisplay().setMap( this.map );
		
		this.fireEvent('afterInit', this, this.map);
	},
	
	codeAddress: function( address, callback ){
		this.geocoder.geocode({'address':address},function(results,status){
			if ( status == google.maps.GeocoderStatus.OK ) {
				callback(results,status);
			} else {
				log(langs('Не удалось определить указанный адрес: ') + status);
			}
		});
	},
	
	initComponent: function(){
		
		var wialon = this;
		
		// assync google map init
		this.mapPanel =	[{
			xtype: 'gmappanel',
//			id: this.getMapId(),
			name: 'GMap',
			gmapType: 'map', // map, panorama
			fillLatLng: true,
			height: this.mapHeight,
			width: this.mapWidth,
			addMarkByClick: true,
			mapOptions: {
				zoom: 15, // specify initial zoom level of the map
				minZoom: 4 // specify minimal zoom level of the map
			},
			markers: [],
			listeners: {
				maprender: function(obj,map){
					wialon.geocoder = new google.maps.Geocoder();
					
					wialon.map = map;
					
					this.initGurtamMaps();
					
					// @todo Сохранить список бригад с ключом = идентификатор
					wialon.getAllAvlUnitsWithCoords(function(res){
						log('1 res', res);
						wialon.units = res.items;
						wialon.fillMapWithUnits();
					});
					
				}.createDelegate(this)
			},
			setCenter: {
				geoCodeAddr: this.city
			}
		}];
		Ext.apply(this,{ items: this.mapPanel });

        this.addEvents('afterInit');
        this.addEvents('afterLoadUnits');
        this.addEvents('afterAddUnitsOnMap');

		sw.Promed.WialonPanel.superclass.initComponent.apply(this,arguments);
	}
});
Ext.reg('swwialonpanel', sw.Promed.WialonPanel );


/**
 * Панель c полями ввода лек.средств с возможностью выбора ввода по торг.наименованию или по МНН,
 * а также с возможностью свернуть поля формы в строку с отображением медикамента
 *
 * @package      libs
 * @access       public
 * @autor		Alexander Permyakov
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @version      12.2013
 */
sw.Promed.TreatDrugPanel = Ext.extend(Ext.Panel, {
    TreatDrugListPanel: null,
    itemIndex: 0,
    selectedDrug: null,
    autoHeight: true,
    layout: 'form',
    border: false,
    defaults: {
        border: false
    },
    findField: function(name) {
        var r = this.fieldsPanel.find('hiddenName',name+this.itemIndex);
        if (!r||!r[0]) {
            r = this.fieldsPanel.find('name',name+this.itemIndex);
        }
        if (!r) {
            log(['findField failed', this, name]);
        }
        return (r && r[0])?r[0]:false;
    },
    getFirstField: function() {
        return this.findField('MethodInputDrug_id');
    },
    setFieldVisible: function(name, flag, allow_clear) {
        var f = this.findField(name);
        if(!f || !f.rendered) {
            return false;
        }
        //log(['setFieldVisible', f, arguments]);
        if (allow_clear) {
            f.setValue(null);
            f.setRawValue('');
        }
        f.setContainerVisible(flag);
        return true;
    },
    setFieldAllowBlank: function(name, flag) {
        var f = this.findField(name);
        if(!f) {
            return false;
        }
        var requiredEditableFields = [
            'MethodInputDrug_id',
            'DrugComplexMnn_id',
            'Drug_id',
            'KolvoEd',
            'Kolvo',
            'EdUnits_id'
        ];
        var cnt = this.TreatDrugListPanel.getCountItems();
        if (!flag && !name.inlist(requiredEditableFields) && cnt > 1) {
            //если поле надо сделать обязательным
            // и его нет в списке обязательных полей
            // и это не единственная видимая панель,
            //то поле должно остаться необязательным
            flag = true;
        }
        f.setAllowBlank(flag);
        if (!flag && f.rendered) {
            f.clearInvalid();
        }
        return true;
    },
    reCount: function(data) {
        if (!data) {
            data = this.TreatDrugListPanel.getRegimeFormParams();
        }
        //log(['reCount', data]);
        var fieldKolvoEd = this.findField('KolvoEd'),
            fieldEdUnits = this.findField('EdUnits_id'),
            fieldKolvo = this.findField('Kolvo');
        // Расчет суточной и курсовой доз
        var dd_text='', kd_text='', dd=0, kd=0, ed = '', rec;
        //Дневная доза – Прием в ед. измерения (либо количество ед. дозировки*дозировку)*Приемов в сутки
        if ( fieldKolvo.getValue() && fieldEdUnits.getValue() ) {
            // в ед. измерения
            dd = data.CountDay*fieldKolvo.getValue();
            rec = fieldEdUnits.getStore().getById(fieldEdUnits.getValue());
            if (rec) {
                ed = rec.get('EdUnits_Name');
                dd_text = dd.toFixed(2) +' '+ ed;
            }
        }
        if (fieldKolvoEd.getValue() && !fieldKolvo.getValue()) {
            // в ед. дозировки только если не указано в ед.измерения
            dd = data.CountDay*fieldKolvoEd.getValue();
            ed = this.findById('TreatDrugForm_Nick'+this.itemIndex).text;
            dd_text = dd.toFixed(2) +' '+ ed;
        }
        if ('EvnCourseTreatDrug' == this.TreatDrugListPanel.objectDrug && dd > 0 && data.Duration>0 && data.ContReception>0) {
            switch (true) {
                case (data.DurationType_id == 2): data.Duration *= 7; break;
                case (data.DurationType_id == 3): data.Duration *= 30; break;
                case (data.DurationType_recid == 2): data.ContReception *= 7; break;
                case (data.DurationType_recid == 3): data.ContReception *= 30; break;
                case (data.Interval > 0 && data.DurationType_intid == 2): data.Interval *= 7; break;
                case (data.Interval > 0 && data.DurationType_intid == 3): data.Interval *= 30; break;
            }
            if (data.Interval > 0) {
                //формула ниже считает исходя из того, что продолжительность включает в себя перерывы
                //kd = dd*(data.Duration-(data.Interval*Math.floor(data.Duration/(data.Interval+data.ContReception))));
                //формула ниже считает исходя из того, что продолжительность равна числу дней приема (на сервере так и считает)
                kd = dd*data.Duration;
            } else {
                kd = dd*data.Duration;
            }
            kd_text=kd.toFixed(2) +' '+ ed;
        }
        this.findField('DoseDay').setValue(dd_text);
        this.findField('PrescrDose').setValue(kd_text);
        this.findField('status').setValue('updated');
        this.tplTreatDrug.overwrite(this.drugViewPanel.body, this.getDrugDataForTpl());
        this.TreatDrugListPanel.setDisabledAddBtn(kd_text.length==0);
    },
    onChangeDrug: function(name, combo, newValue){
        var record = combo.getStore().getById(newValue);
        if (!record) {
            log('fail onChangeDrug');
            return false;
        }
        var fieldKolvoEd = this.findField('KolvoEd'),//Ед. дозировки
            fieldKolvo = this.findField('Kolvo'),
            fieldEdUnits = this.findField('EdUnits_id'),
            kolvo = null,
            EdUnits_id = null,
            kolvoEd = null,
            drugform_name,
            dose;
        if ('Drug_id' == name) {
            dose = record.get('Drug_Dose')||'';
            drugform_name = record.get('DrugForm_Name')||'';
        } else {
            dose = record.get('DrugComplexMnn_Dose')||'';
            drugform_name = record.get('RlsClsdrugforms_Name')||'';
        }
        this.doseData = this.parseDose(dose);
        if (this.doseData.kolvo) {
            kolvoEd = 1;
            kolvo = this.doseData.kolvo;
        }
        if (this.doseData.EdUnits_id) {
            EdUnits_id = this.doseData.EdUnits_id;
        }
        fieldEdUnits.setValue(EdUnits_id);
        fieldKolvo.setValue(kolvo);
        fieldKolvoEd.setValue(kolvoEd);
        this.setDrugForm(drugform_name);
        //this.findField('DoseDay').setValue('');
        //this.findField('PrescrDose').setValue('');
        this.reCount();
        return true;
    },
    doseData: {
        kolvo: null,
        EdUnits_id: null
    },
    /**
     * @param {String} dose
     * @return {Object}
     */
    parseDose: function(dose){
        var doseData = {
            kolvo: null,
            EdUnits_id: null
        };
        dose = dose.trim();
        if (dose.length==0) {
            return doseData;
        }
        var res = dose.match(/^[\d\.]+/i);
        if (res.length==0) {
            return doseData;
        }
        doseData.kolvo = res[0];
        var ed = dose.replace(res[0] ,"").trim();
        this.TreatDrugListPanel._edUnitsStore.each(function(rec){
            //log([rec.get('EdUnits_Name'),ed]);
            if (rec.get('EdUnits_Name')==ed) {
                doseData.EdUnits_id = rec.get('EdUnits_id');
                return false;
            }
            return true;
        });
        return doseData;
    },
    /**
     * Срабатывает после onChangeDrug или после загрузки панели
     * @param name
     * @param combo
     * @param record
     */
    onSelectDrug: function(name, combo, record){
        this.selectedDrug = record;
        //log(['onSelectDrug', record]);
        var drugform_name,
            dose;
        if ('Drug_id' == name) {
            dose = record.get('Drug_Dose')||'';
            if (record.get('Drug_Name')) {
                drugform_name = record.get('Drug_Name');
            }
            if (record.get('DrugForm_Name')) {
                drugform_name = record.get('DrugForm_Name');
            }
        } else {
            dose = record.get('DrugComplexMnn_Dose')||'';
            if (record.get('DrugComplexMnn_Name')) {
                drugform_name = record.get('DrugComplexMnn_Name');
            }
            if (record.get('RlsClsdrugforms_Name')) {
                drugform_name = record.get('RlsClsdrugforms_Name');
            }
        }
        this.setDrugForm(drugform_name);
        this.doseData = this.parseDose(dose);
    },
    setDrugForm: function(drugform_name) {
        var fDrugForm = this.findField('DrugForm_Name');
        //log('setDrugForm');
        //log(drugform_name);
        if (drugform_name && fDrugForm) {
            this.findField('DrugForm_Name').setValue(drugform_name);
        }
        var drugform_nick = '';
        if (typeof drugform_name == 'string') {
            drugform_name = drugform_name.toLowerCase();
            if(drugform_name.indexOf('капл') >= 0) {
                drugform_nick = 'капли';
            }
            if(drugform_name.indexOf('капс') >= 0) {
                drugform_nick = 'капс.';
            }
            if(drugform_name.indexOf('супп') >= 0) {
                drugform_nick = 'супп.';
            }
            if(drugform_name.indexOf('табл') >= 0) {
                drugform_nick = 'табл.';
            }
        }
        this.findById('TreatDrugForm_Nick'+this.itemIndex).setText(drugform_nick);
    },
    onClearDrug: function(){
        this.selectedDrug = null;
        this.doseData = this.parseDose('');
        this.findField('DrugComplexMnn_id').setValue(null);
        this.findField('Drug_id').setValue(null);
        this.findField('KolvoEd').setValue(null);
        this.findField('Kolvo').setValue(null);
        this.findField('EdUnits_id').setValue(null);
        this.findField('DrugForm_Name').setValue('');
        this.findById('TreatDrugForm_Nick'+this.itemIndex).setText('');
        this.reCount();
    },
    onSelectMethodInputDrug: function(value, allow_clear) {
        this.setFieldVisible('DrugComplexMnn_id', (value == 1), allow_clear);
        this.setFieldAllowBlank('DrugComplexMnn_id', (value != 1));
        this.setFieldVisible('Drug_id', (value != 1), allow_clear);
        this.setFieldAllowBlank('Drug_id', (value == 1));
        this.TreatDrugListPanel._syncSize();
        if (allow_clear) {
            this.onClearDrug();
        }
    },
    loadCombo: function(combo, params, callback) {
        combo.getStore().load({
            callback: function() {
                if ( combo.getStore().getCount() > 0 && combo.getValue() > 0 ) {
                    combo.setValue(combo.getValue());
                    if (typeof callback == 'function') {
                        var rec = combo.getStore().getById(combo.getValue());
                        callback(rec);
                    }
                }
            },
            params: params
        });
    },
    getDrugDataForTpl: function() {
        var params = this.TreatDrugListPanel.getRegimeFormParams();
        var data = {};
        if (1 == this.findField('MethodInputDrug_id').getValue()) {
            data['Drug_Name'] = this.findField('DrugComplexMnn_id').getRawValue() || null;
        } else {
            data['Drug_Name'] = this.findField('Drug_id').getRawValue() || null;
        }
        data['KolvoEd'] = this.findField('KolvoEd').getValue() || null;
        data['DrugForm_Nick'] = this.findById('TreatDrugForm_Nick'+this.itemIndex).text || null;
        data['Kolvo'] = this.findField('Kolvo').getValue() || null;
        data['EdUnits_Nick'] = this.findField('EdUnits_id').getRawValue() || null;
        data['DoseDay'] = this.findField('DoseDay').getValue() || null;
        data['setDate'] = null;
        data['Duration'] = null;
        data['DurationType_Nick'] = null;
        data['PrescrDose'] = null;
        data['FactCount'] = 0;
        if ('EvnCourseTreatDrug' == this.TreatDrugListPanel.objectDrug) {
            data['setDate'] = params.setDate || null;
            data['Duration'] = params.Duration || null;
            data['DurationType_Nick'] = params.DurationType_Nick || null;
            data['PrescrDose'] = this.findField('PrescrDose').getValue();
        }
        return data;
    },
    getDrugData: function() {
        var data = {};
        data['FactCount'] = 0;
        data['status'] = this.findField('status').getValue();
        data['MethodInputDrug_id'] = this.findField('MethodInputDrug_id').getValue();
        data['DrugComplexMnn_id'] = this.findField('DrugComplexMnn_id').getValue() || null;
        data['Drug_id'] = this.findField('Drug_id').getValue() || null;
        data['DrugForm_Name'] = this.findField('DrugForm_Name').getValue();
        data['EdUnits_id'] = this.findField('EdUnits_id').getValue() || null;
        if ('EvnCourseTreatDrug' == this.TreatDrugListPanel.objectDrug) {
            data['PrescrDose'] = this.findField('PrescrDose').getValue();
        }
        data['id'] = this.findField('id').getValue() || null;
        data['KolvoEd'] = this.findField('KolvoEd').getValue() || null;
        data['Kolvo'] = this.findField('Kolvo').getValue() || null;
        data['DoseDay'] = this.findField('DoseDay').getValue();
        if ( !data['MethodInputDrug_id']
            || (2 == data['MethodInputDrug_id'] && !data['Drug_id'])
            || (1 == data['MethodInputDrug_id'] && !data['DrugComplexMnn_id'])
            /* При открытии назначения по стандарту эти поля пустые
            || !data['KolvoEd']
            || !data['Kolvo']
            || !data['EdUnits_id']
            || !data['DoseDay']
            || ('EvnCourseTreatDrug' == this.TreatDrugListPanel.objectDrug && !data['PrescrDose'])*/
            ) {
            log(['is not valid data', data]);
            switch (true) {
                case (this.TreatDrugListPanel.firstNotValidField):
                    //Поле уже определили, ничего делать не надо
                    break;
                case (this.fieldsPanel.hidden):
                    //Панель скрыта, ничего делать не надо
                    break;
                case (!data['MethodInputDrug_id']):
                    this.TreatDrugListPanel.firstNotValidField = this.findField('MethodInputDrug_id');
                    break;
                case (2 == data['MethodInputDrug_id'] && !data['Drug_id']):
                    this.TreatDrugListPanel.firstNotValidField = this.findField('Drug_id');
                    break;
                case (1 == data['MethodInputDrug_id'] && !data['DrugComplexMnn_id']):
                    this.TreatDrugListPanel.firstNotValidField = this.findField('DrugComplexMnn_id');
                    break;
                case (!data['KolvoEd']):
                    this.TreatDrugListPanel.firstNotValidField = this.findField('KolvoEd');
                    break;
                case (!data['Kolvo']):
                    this.TreatDrugListPanel.firstNotValidField = this.findField('Kolvo');
                    break;
                case (!data['EdUnits_id']):
                    this.TreatDrugListPanel.firstNotValidField = this.findField('EdUnits_id');
                    break;
            }
            return false;
        }
        return data;
    },
    applyDrugData: function(data, afterApply) {
        var thas = this,
            allowClear = false,
            enableEdit = thas.TreatDrugListPanel.getEnableEdit(),
            f = this.findField('MethodInputDrug_id'),
            f2 = this.findField('DrugComplexMnn_id'),
            f3 = this.findField('Drug_id'),
            fDrugForm = this.findField('DrugForm_Name'),
            fEdUnits = this.findField('EdUnits_id'),
            fId = this.findField('id'),
            fStatus = this.findField('status'),
            fKolvoEd = this.findField('KolvoEd'),
            fKolvo = this.findField('Kolvo'),
            fDoseDay = this.findField('DoseDay'),
            fPrescrDose = this.findField('PrescrDose');
        if (!f) {
            log('fail applyDrugData');
            return false;
        }
        this.setFieldAllowBlank('MethodInputDrug_id', false);
        this.setFieldAllowBlank('Drug_id', false);
        this.setFieldAllowBlank('DrugComplexMnn_id', false);
        this.setFieldAllowBlank('EdUnits_id', false);
        this.setFieldAllowBlank('KolvoEd', false);
        this.setFieldAllowBlank('Kolvo', false);
        //log(['applyDrugData', data]);
        if (!data || !data.MethodInputDrug_id) {
            fStatus.setValue('new');
            f.setValue(thas.TreatDrugListPanel.defaultMethodInputDrug_id);
            allowClear = true;
        } else {
            if (!data.status) {
                fStatus.setValue('saved');
            } else {
                fStatus.setValue(data.status);
            }
            f.setValue(data.MethodInputDrug_id);
            f2.setValue(data.DrugComplexMnn_id||null);
            f3.setValue(data.Drug_id||null);
            if (data.Drug_Name) {
                if (1 == data.MethodInputDrug_id) {
                    if (f2.rendered) {
                        f2.setRawValue(data.Drug_Name);
                    } else {
                        f2.on('render', function(){
                            f2.setRawValue(data.Drug_Name);
                        });
                    }
                } else {
                    if (f3.rendered) {
                        f3.setRawValue(data.Drug_Name);
                    } else {
                        f3.on('render', function(){
                            f3.setRawValue(data.Drug_Name);
                        });
                    }
                }
            }
            fDrugForm.setValue(data.DrugForm_Name||null);
            fEdUnits.setValue(data.EdUnits_id||null);
            if (data.EdUnits_Nick) {
                if (fEdUnits.rendered) {
                    fEdUnits.setRawValue(data.EdUnits_Nick);
                } else {
                    fEdUnits.on('render', function(){
                        fEdUnits.setRawValue(data.EdUnits_Nick);
                    });
                }
            }
            fId.setValue(data['id']||null);
            fKolvoEd.setValue(data['KolvoEd']||null);
            fKolvo.setValue(data['Kolvo']||null);
            fDoseDay.setValue(data['DoseDay']||null);
            fPrescrDose.setValue(data['PrescrDose']||null);
        }

        f.setDisabled(!enableEdit||'EvnCourseTreatDrug' != this.TreatDrugListPanel.objectDrug);
        f2.setDisabled(!enableEdit||'EvnCourseTreatDrug' != this.TreatDrugListPanel.objectDrug);
        f3.setDisabled(!enableEdit||'EvnCourseTreatDrug' != this.TreatDrugListPanel.objectDrug);
        fEdUnits.setDisabled(!enableEdit||'EvnCourseTreatDrug' != this.TreatDrugListPanel.objectDrug);
        fKolvoEd.setDisabled(!enableEdit);
        fKolvo.setDisabled(!enableEdit);

        var baseParams = {
            LpuSection_id: thas.TreatDrugListPanel.LpuSection_id,
            isFromDocumentUcOst: 'off'
        };
        if ( thas.TreatDrugListPanel.parentEvnClass_SysNick && thas.TreatDrugListPanel.parentEvnClass_SysNick == 'EvnSection' ) {
            baseParams.isFromDocumentUcOst = 'on';
        }
        f2.getStore().baseParams = baseParams;
        f3.getStore().baseParams = baseParams;
        if (f.getValue() == 1) {
            if (f2 && f2.getValue()) {
                this.loadCombo(f2, {
                    DrugComplexMnn_id: f2.getValue()
                }, function (rec) {
                    if (rec) {
                        f2.fireEvent('select', f2, rec);
                    }
                    thas.onSelectMethodInputDrug(f.getValue(), allowClear);
                    afterApply();
                });
            } else {
                log('fail field DrugComplexMnn_id - is not loadCombo');
                thas.onSelectMethodInputDrug(f.getValue(), allowClear);
                afterApply();
            }
        } else {
            if (f3 && f3.getValue()) {
                this.loadCombo(f3, {
                    Drug_id: f3.getValue()
                }, function (rec) {
                    if (rec) {
                        f3.fireEvent('select', f3, rec);
                    }
                    thas.onSelectMethodInputDrug(f.getValue(), allowClear);
                    afterApply();
                });
            } else {
                log('fail field Drug_id - is not loadCombo');
                thas.onSelectMethodInputDrug(f.getValue(), allowClear);
                afterApply();
            }
        }
        return true;
    },
    tplTreatDrug: new Ext.XTemplate(
        '<p>',
        '<tpl if="Drug_Name">',
        '<strong>{Drug_Name}</strong>',
        '</tpl>',
        '<tpl if="setDate">',
        ' С {setDate}',
        '</tpl>',
        '<tpl if="Duration&&DurationType_Nick">',
        ' продолжительность {Duration} {DurationType_Nick}.',
        '</tpl>',
        '<tpl if="Kolvo&&EdUnits_Nick">',
        '<br>Доза разовая - {Kolvo} {EdUnits_Nick}',
        '</tpl>',
        '<tpl if="!Kolvo&&!EdUnits_Nick&&KolvoEd&&DrugForm_Nick">',
        '<br>Доза разовая - {KolvoEd} {DrugForm_Nick}',
        '</tpl>',
        '<tpl if="DoseDay">',
        '; дневная - {DoseDay}',
        '</tpl>',
        '<tpl if="PrescrDose">',
        '; курсовая - {PrescrDose}.',
        '</tpl>',
        '<tpl if="!PrescrDose">',
        '.',
        '</tpl>',
        '</p>'
    ),
    isDisabledDelBtn: true,
    setDisabledDelBtn: function(disabled) {
        if (!disabled) {
            //кнопка удалить должна быть не доступна, если только один медикамент
            disabled = (this.TreatDrugListPanel.getCountItems()==1);
        }
        this.isDisabledDelBtn = disabled;
    },
    toogleViewPanel: function(isShowDrugViewPanel) {
        if (isShowDrugViewPanel) {
            this.fieldsPanel.hide();
            this.tplTreatDrug.overwrite(this.drugViewPanel.body, this.getDrugDataForTpl());
            this.drugViewPanel.show();
        } else {
            this.drugViewPanel.hide();
            this.fieldsPanel.show();
        }
        this.TreatDrugListPanel._syncSize();
    },
    initComponent: function() {
        var thas = this;

        this.bodyStyle = this.TreatDrugListPanel.bodyStyle;

        this.fieldsPanel = new Ext.Panel({
            title: langs('Медикамент: Редактирование'),
            collapsible: false,
            tools: [{
                id:'toggle',
                qtip: langs('Свернуть'),
                handler: function() {
                    thas.toogleViewPanel(true);
                }
            }],
            listeners: {
                render: function(panel) {
                    if (panel.header)
                    {
                        panel.header.on({
                            'click': {
                                fn: function() {
                                    thas.toogleViewPanel(true);
                                },
                                scope: panel
                            },
                            'mouseover': {
                                fn: function() {
                                    this.applyStyles('cursor: pointer');
                                },
                                scope: panel.header
                            },
                            'mouseout': {
                                fn: function() {
                                    this.applyStyles('cursor: default');
                                },
                                scope: panel.header
                            }
                        });
                    }
                }
            },
            layout: 'form',
            autoHeight: this.autoHeight,
            border: true,
            labelWidth: this.labelWidth,
            labelAlign: this.labelAlign,
            bodyStyle: thas.TreatDrugListPanel.itemBodyStyle,
            items: [{
                name: 'id'+this.itemIndex,
                value: null,
                xtype: 'hidden'
            }, {
                name: 'status'+this.itemIndex,
                value: null,
                xtype: 'hidden'
            }, {
                name: 'DrugForm_Name'+this.itemIndex,
                value: null,
                xtype: 'hidden'
            }, {
                hiddenName: 'MethodInputDrug_id'+this.itemIndex,
                fieldLabel: langs('Назначение'),
                value: this.TreatDrugListPanel.defaultMethodInputDrug_id,
                anchor: '96%',
                listeners: {
                    select: function(combo) {
                        thas.onSelectMethodInputDrug(combo.getValue(),true);
                    }
                },
                xtype: 'swmethodinputdrugcombo'
            }, {
                hiddenName: 'DrugComplexMnn_id'+this.itemIndex,
                onClearValue: this.onClearDrug,
                anchor: '96%',
                listeners: {
                    change: function(combo, newValue) {
                        thas.onChangeDrug('DrugComplexMnn_id', combo, newValue);
                    },
                    select: function(combo, record) {
                        thas.onSelectDrug('DrugComplexMnn_id', combo, record);
                    }
                },
                xtype: 'swdrugcomplexmnncombo'
            }, {
                hiddenName: 'Drug_id'+this.itemIndex,
                onClearValue: this.onClearDrug,
                anchor: '96%',
                listeners: {
                    change: function(combo, newValue) {
                        thas.onChangeDrug('Drug_id', combo, newValue);
                    },
                    select: function(combo, record) {
                        thas.onSelectDrug('Drug_id', combo, record);
                    }
                },
                xtype: 'swdrugsimplecombo'
            },{
                autoHeight: true,
                title: langs('На один прием'),
                xtype: 'fieldset',
                border: true,
                items: [{
                    border: false,
                    layout: 'column',
                    items: [{
                        border: false,
                        labelWidth: 120,
                        layout: 'form',
                        items: [{
                            name: 'KolvoEd'+this.itemIndex,
                            allowDecimals: true,
                            allowNegative: false,
                            decimalPrecision: 2,
                            fieldLabel: langs('Ед. дозировки'),
                            style: 'text-align: right;',
                            listeners: {
                                change: function(fieldKolvoEd, newValue) {
                                    var fieldKolvo = thas.findField('Kolvo'),
                                        fieldEdUnits = thas.findField('EdUnits_id'),
                                        kolvo = null,
                                        kolvoEd = null;
                                    if (!newValue) {
                                        fieldKolvo.setValue(kolvo);
                                        fieldKolvoEd.setValue(kolvoEd);
                                    } else if (thas.doseData.kolvo) {
                                        kolvoEd = newValue;
                                        kolvo = thas.doseData.kolvo*newValue;
                                        fieldKolvo.setValue(kolvo);
                                        fieldKolvoEd.setValue(kolvoEd);
                                        if (thas.doseData.EdUnits_id) {
                                            fieldEdUnits.setValue(thas.doseData.EdUnits_id);
                                        }
                                    }
                                    thas.reCount();
                                }
                            },
                            width: 60,
                            xtype: 'numberfield'
                        }]
                    },{
                        border: false,
                        layout: 'form',
                        items: [
                            new Ext.form.Label({
                                id: 'TreatDrugForm_Nick'+this.itemIndex,
                                style: 'padding: 0; padding-left: 5px; font-size: 9pt;',
                                width: 60,
                                text: ''
                            })
                        ]
                    },{
                        border: false,
                        layout: 'form',
                        items: [{
                            name: 'Kolvo'+this.itemIndex,
                            allowDecimals: true,
                            allowNegative: false,
                            decimalPrecision: 4,
                            fieldLabel: langs('Ед.измерения'),
                            style: 'text-align: right;',
                            listeners: {
                                change: function(fieldKolvo, newValue) {
                                    var fieldKolvoEd = thas.findField('KolvoEd'),
                                        fieldEdUnits = thas.findField('EdUnits_id'),
                                        kolvo = null,
                                        kolvoEd = null;
                                    if (!newValue) {
                                        fieldKolvo.setValue(kolvo);
                                        fieldKolvoEd.setValue(kolvoEd);
                                    } else if (thas.doseData.kolvo) {
                                        kolvo = newValue;
                                        kolvoEd = newValue/thas.doseData.kolvo;
                                        fieldKolvo.setValue(kolvo);
                                        fieldKolvoEd.setValue(kolvoEd);
                                        if (thas.doseData.EdUnits_id) {
                                            fieldEdUnits.setValue(thas.doseData.EdUnits_id);
                                        }
                                    }
                                    thas.reCount();
                                }
                            },
                            minValue: 0.0001,
                            width: 60,
                            xtype: 'numberfield'
                        }]
                    },{
                        border: false,
                        layout: 'form',
                        items: [{
                            hiddenName: 'EdUnits_id'+this.itemIndex,
                            hideLabel: true,
                            width: 100,
                            listeners: {
                                change: function() {
                                    thas.reCount();
                                }
                            },
                            xtype: 'swedunitscombo'
                        }]
                    }]
                }]
            },{
                border: false,
                layout: 'column',
                items: [{
                    border: false,
                    labelWidth: this.labelWidth,
                    layout: 'form',
                    items: [{
                        name: 'DoseDay'+this.itemIndex,
                        fieldLabel: langs('Дневная доза'),
                        disabled: true,
                        width: 100,
                        xtype: 'textfield'
                    }]
                },{
                    border: false,
                    layout: 'form',
                    id: 'wrap_PrescrDose'+this.itemIndex,
                    items: [{
                        name: 'PrescrDose'+this.itemIndex,
                        fieldLabel: langs('Курсовая доза'),
                        disabled: true,
                        width: 100,
                        xtype: 'textfield'
                    }]
                }]
            }]
        });

        this.drugViewPanel = new Ext.Panel({
            title: langs('Медикамент: просмотр'),
            collapsible: false,
            tools: [{
                id:'toggle',
                qtip: langs('Редактировать'),
                handler: function() {
                    if (thas.TreatDrugListPanel.getEnableEdit()) {
                        thas.toogleViewPanel(false);
                    }
                }
            },{
                id:'close',
                qtip: langs('Удалить'),
                handler: function() {
                    if (thas.TreatDrugListPanel.getEnableEdit() && !thas.isDisabledDelBtn) {
                        thas.findField('status').setValue('deleted');
                        thas.hide();
                        thas.TreatDrugListPanel._syncSize();
                        thas.TreatDrugListPanel.onRemoveItem();
                    }
                }
            }],
            listeners: {
                render: function(panel) {
                    if (panel.header)
                    {
                        panel.header.on({
                            'click': {
                                fn: function() {
                                    if (thas.TreatDrugListPanel.getEnableEdit()) {
                                        thas.toogleViewPanel(false);
                                    }
                                },
                                scope: panel
                            },
                            'mouseover': {
                                fn: function() {
                                    this.applyStyles('cursor: pointer');
                                },
                                scope: panel.header
                            },
                            'mouseout': {
                                fn: function() {
                                    this.applyStyles('cursor: default');
                                },
                                scope: panel.header
                            }
                        });
                    }
                }
            },
            layout: 'form',
            bodyStyle: thas.TreatDrugListPanel.itemBodyStyle,
            border: false,
            autoHeight: true,
            hidden: true,
            items: [{
                html: ''
            }]
        });

        Ext.apply(this, {
            items: [
                this.fieldsPanel,
                this.drugViewPanel
            ]
        });
        sw.Promed.TreatDrugPanel.superclass.initComponent.apply(this, arguments);

        /*
        this.findField('Drug_id').on('render', function(){
            var f = thas.findField('MethodInputDrug_id');
            thas.onSelectMethodInputDrug(f.getValue(), !thas.getDrugData());
            log('onrender');
        });
        */

        if ('EvnCourseTreatDrug' != this.TreatDrugListPanel.objectDrug) {
            this.findById('wrap_PrescrDose'+this.itemIndex).hide();
        }
    }
});

/**
 * Панель списка медикаментов в курсе или назначении ЛС
 * @package      libs
 * @access       public
 * @autor		Alexander Permyakov
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @version      12.2013
 */
sw.Promed.TreatDrugListPanel = Ext.extend(Ext.Panel, {
    win: null,
    form: null,
    defaultMethodInputDrug_id: 1,
    objectDrug: 'EvnCourseTreatDrug',
    _arrDrugListData: [],
    disabledAddDrug: false,
    confAddBtn: {},
    lastItemsIndex: 0,
    limitCountItem: 3,

    LpuSection_id: null,
    parentEvnClass_SysNick: 'EvnSection',

    autoHeight: true,
    labelAlign: 'right',
    layout: 'form',
    labelWidth: 130,
    border: false,
    defaults: {
        border: false
    },
    itemBodyStyle: 'padding: 0',
    getRegimeFormParams: function() {
        //override!
        return {
            setDate: null,
            CountDay: 1,
            Duration: 1,
            DurationType_id: 1,
            DurationType_Nick: 'дн.',
            ContReception: 1,
            DurationType_recid: 1,
            Interval: 0,
            DurationType_intid: 1
        };
    },
    reCountAll: function() {
        this.items.each(function(item) {
            if (item.isVisible()) {
                item.reCount();
            }
        },this);
    },
    _syncSize: function() {
        this.doLayout();
        this.syncSize();
        if(this.win) {
            var form;
            if (this.win.FormPanel) {
                form = this.win.FormPanel;
            } else if(this.form_id) {
                form = this.win.findById(this.form_id);
            }
            if (form) {
                //form.getForm().clearInvalid();
                form.doLayout();
                form.syncSize();
            }
            this.win.doLayout();
            this.win.syncSize();
            this.win.center();
        }
    },
    reset: function() {
        //this.setDrugListData('[0]');
    },
    onLoadForm: function(str) {
        if (!str) {
            str = '[0]';
        }
        this.setDrugListData(str);
    },
    getDrugListData: function() {
        var data = [];
        this.firstNotValidField = null;
        this.items.each(function(item) {
            var d = item.getDrugData();
            if (d) {
                data.push(d);
            }
            return true;
        },this);
        return data;
    },
    getEnableEdit: function() {
        return this._enableEdit||false;
    },
    setEnableEdit: function(enable) {
        this._enableEdit = enable;
    },
    setDrugListData: function(str) {
        //log('setDrugListData');
        //log(str);
        var data = [0];
        try {
            data = Ext.util.JSON.decode(str);
        } catch (e) {
            data = [0];
        }
        //log(data);
        if (Ext.isArray(data)) {
            this._arrDrugListData = data;
        } else {
            this._arrDrugListData = [0];
        }
        this.items.each(function(item) {
            item.hide();
            this.remove(item,true);
        },this);

        var i=0;
        var thas = this;
        var beforeAddItem = function(){
            var data = false;
            if (i<thas._arrDrugListData.length) {
                data = thas._arrDrugListData[i];
                if (typeof data != 'object') data = {};
            }
            //log(['beforeAddItem', i, thas._arrDrugListData.length, data]);
            return data;
        };
        function afterAddItem(){
            i++;
            data = beforeAddItem();
            if (typeof data == 'object') {
                thas._addItem(false, data, afterAddItem);
            }
        }

        data = beforeAddItem();
        if (typeof data == 'object') {
            this._addItem(true, data, afterAddItem);
        }
        /*
        for (var i=0; i<this._arrDrugListData.length; i++) {
            data = this._arrDrugListData[i];
            log(['setDrugListData', i, data]);
            if (typeof data != 'object') data = null;
            if (!this._addItem((i==0), data)) break;
        }*/
    },
    onRemoveItem: function() {
        this.items.each(function(item) {
            if (item.isVisible()) {
                item.setDisabledDelBtn(false);
            }
            return true;
        },this);
    },
    getCountItems: function() {
        //this.items.getCount() - не подходит
        var cnt = 0;
        this.items.each(function(item) {
            if (item.isVisible()) {
                cnt++;
            }
            return true;
        },this);
        return cnt;
    },
    isValidAllItems: function() {
        var isValidAllItems = true;
        this.items.each(function(item) {
            if (item.isVisible() && !item.getDrugData()) {
                isValidAllItems = false;
            }
            return isValidAllItems;
        },this);
        return isValidAllItems;
    },
    setDisabledAddBtn: function(disabled)
    {
        if (this.buttons && this.buttons[0]) {
            if (!disabled) {
                disabled = (
                    this.getCountItems()==this.limitCountItem
                    || !this.isValidAllItems()
            );
            }
            this.buttons[0].setDisabled(disabled);
        }
    },
    _addItem: function(is_first, data, afterAddItem)
    {
        if (this.getCountItems()==this.limitCountItem ) {
            this.setDisabledAddBtn(true);
            return false;
        } else {
            this.setDisabledAddBtn(false);
        }
        if (is_first)
            this.lastItemsIndex = 0;
        else
            this.lastItemsIndex++;
        var conf = {
            TreatDrugListPanel: this,
            labelWidth: this.labelWidth,
            labelAlign: this.labelAlign,
            itemIndex: this.lastItemsIndex
        };
        var item = this.add(new sw.Promed.TreatDrugPanel(conf));
        //log(['_addItem', this.lastItemsIndex, data, item]);
        this._loadEdUnitsStore(item, function(item){
            item.applyDrugData(data, function(){
                //нужно сворачивать в строку все кроме последнего, если нажали добавить медикамент (!data)
                item.TreatDrugListPanel.items.each(function(it) {
                    if (it.getDrugData()) {
                        it.toogleViewPanel(true);
                    } else {
                        it.toogleViewPanel(false);
                        it.onSelectMethodInputDrug(it.TreatDrugListPanel.defaultMethodInputDrug_id, true);
                    }
                },item.TreatDrugListPanel);
                item.setDisabledDelBtn(!item.TreatDrugListPanel.getEnableEdit());
                if (typeof afterAddItem == 'function') {
                    afterAddItem();
                }
            });
        });
        return item;
    },
    _loadEdUnitsStore: function(item, callback)
    {
        if (!this._edUnitsStore) {
            this._edUnitsStore = new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'EdUnits_id'
                }, [
                    {name: 'EdUnits_id', mapping: 'EdUnits_id'},
                    {name: 'EdUnits_Code', mapping: 'EdUnits_Code', type: 'int'},
                    {name: 'EdUnits_Name', mapping: 'EdUnits_Name'},
                    {name: 'EdUnits_FullName', mapping: 'EdUnits_FullName'}
                ]),
                url: '/?c=EvnPrescr&m=loadEdUnitsList'
            });
        }
        if (this._edUnitsStore.getCount()>0) {
            item.findField('EdUnits_id').getStore().loadData(getStoreRecords(this._edUnitsStore));
            callback(item);
        } else {
            this._edUnitsStore.load({
                callback: function() {
                    item.findField('EdUnits_id').getStore().loadData(getStoreRecords(item.TreatDrugListPanel._edUnitsStore));
                    callback(item);
                }
            });
        }
    },
    initComponent: function()
    {
        var thas = this;
        this.bodyStyle = 'padding: 0;';
        if (!this.disabledAddDrug) {
            if (!this.confAddBtn.iconCls) {
                this.confAddBtn.iconCls = 'add16';
            }
            if (!this.confAddBtn.text) {
                this.confAddBtn.text = langs('Добавить медикамент');
            }
            this.confAddBtn.handler = function() {
                thas._addItem();
            };
            this.buttons = [this.confAddBtn];
        }
        sw.Promed.TreatDrugListPanel.superclass.initComponent.apply(this, arguments);
    }
});


/**
 * Класс представления списка медикаментов из назначений в курсе
 */
sw.Promed.EvnPrescrTreatDrugDataView = function(id, renderTo, data, emk, section_code, d) {
    var thas = this;
    if (!id || !renderTo || !data || !emk) {
        return this;
    }
    thas.emk = emk;
    thas.sectionCode = section_code||'';
    thas.elData = d||{};
    thas.id = id||'EvnPrescrTreatDrugDataView';
    thas.dataView = null;
    thas.drugList = [];
    thas.begDate = null;
    thas.curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
    this.validateEvnCourseData = function(ec_data) {
        if (!ec_data || !ec_data.EvnCourse_id || !ec_data.PrescrListData|| !ec_data.EvnCourse_begDate  ) {
            return false;
        }
        return (typeof ec_data.PrescrListData == 'object');
    };
    this.applyEvnCourseData = function(ec_data) {
        thas.initialEvnCourseData = ec_data;
        thas.begDate = Date.parseDate(ec_data.EvnCourse_begDate, 'd.m.Y');
        thas.drugList = [];
        var drug;
        var ep_id, ep_data = null;
        var dr_id, dr_data = null;
        for (ep_id in ec_data.PrescrListData) {
            ep_data = ec_data.PrescrListData[ep_id];
            for (dr_id in ep_data.DrugListData) {
                dr_data = ep_data.DrugListData[dr_id];
                drug = {};
                drug.EvnPrescrTreatDrug_id = dr_id;
                drug.Drug_key = dr_data.Drug_key;
                drug.Drug_Name = dr_data.Drug_Name;
                drug.DrugTorg_Name = dr_data.DrugTorg_Name;
                drug.KolvoEd = dr_data.KolvoEd;
                drug.DrugForm_Nick = dr_data.DrugForm_Nick;
                drug.Kolvo = dr_data.Kolvo;
                drug.EdUnits_Nick = dr_data.EdUnits_Nick;
                drug.DoseDay = dr_data.DoseDay;
                drug.PrescrCntDay = dr_data.PrescrCntDay;
                drug.FactCntDay = dr_data.FactCntDay;
                drug.EvnPrescr_id = ep_id;
                drug.EvnPrescr_pid = ep_data.EvnPrescr_pid;
                drug.EvnPrescr_rid = ep_data.EvnPrescr_rid;
                drug.EvnCourse_id = ec_data.EvnCourse_id;
                drug.PrescriptionType_id = ep_data.PrescriptionType_id;
                drug.PrescrFailureType_id = ep_data.PrescrFailureType_id;
                drug.PrescriptionType_Code = ep_data.PrescriptionType_Code;
                drug.EvnPrescr_setDate = ep_data.EvnPrescr_setDate;//Date.parseDate(ep_data.EvnPrescr_setDate, 'd.m.Y');
                drug.dayNum = '';
                drug.EvnPrescr_IsHasEvn = ep_data.EvnPrescr_IsHasEvn;
                drug.PrescriptionStatusType_id = ep_data.PrescriptionStatusType_id;
                drug.EvnPrescr_IsExec = ep_data.EvnPrescr_IsExec;
                thas.drugList.push(drug);
            }
        }
        if (thas.dataView) {
            thas.dataView.getStore().baseParams.EvnCourse_id = ec_data.EvnCourse_id;
            thas.dataView.getStore().loadData(thas.drugList, true);
            thas.dataView.setDayNum();
        }
    };

    var thStyle = 'font-size: 10px; font-weight: normal; padding: 1px; border-width: 0; text-align: left;';

    var trDrugStyle = 'background: none; cursor:pointer;';
    var tdDrugNameStyle = 'font-size: 10px; font-weight: bold; padding: 1px; border-width: 1px; text-align: left';
    var tdDefaultStyle = 'font-size: 10px; font-weight: bold; padding: 1px; border-width: 1px; text-align: center;';

    var tdDayNumStyleTpl = '<tpl if="!this.isCurDate(EvnPrescr_setDate)"> background: none;</tpl>'+
        '<tpl if="this.isCurDate(EvnPrescr_setDate)"> background: gray; color: #fff;</tpl>';
    var tdDayNumStyle = tdDefaultStyle+tdDayNumStyleTpl;

    var gTipTpl = ' title="Отфильтровать по этому элементу"';
    var onCellEventsTpl = ' onclick="Ext.getCmp(\''+thas.id+'\').toogleDrug(\'{Drug_key}\');"'+
        ' onmouseover="Ext.getCmp(\''+thas.id+'\').selectDrugRow(\'DrugRow{EvnPrescrTreatDrug_id}\');"'+
        ' onmouseout="Ext.getCmp(\''+thas.id+'\').unSelectDrugRow(\'DrugRow{EvnPrescrTreatDrug_id}\');"';
    var onRowEventsTpl = '';

    var dayNumTpl = '<span>{dayNum}</span>';
    var drugNameTpl = '<span>{DrugTorg_Name}</span>';
    var doseDayTpl = '<span>{DoseDay}</span>';

    var doseOneTpl = '<span>'+
        '<tpl if="Kolvo&&EdUnits_Nick">{Kolvo} {EdUnits_Nick}</tpl>'+
        '<tpl if="!Kolvo&&!EdUnits_Nick&&KolvoEd&&DrugForm_Nick">{KolvoEd} {DrugForm_Nick}</tpl>'+
        '<tpl if="!Kolvo&&!EdUnits_Nick&&!KolvoEd&&!DrugForm_Nick">-</tpl>'+
        '</span>';
    var cntDayTpl = '<span>'+
        '<tpl if="!PrescrCntDay">0</tpl>'+
        '<tpl if="PrescrCntDay&&FactCntDay">{FactCntDay}/{PrescrCntDay}</tpl>'+
        '<tpl if="PrescrCntDay&&!FactCntDay">0/{PrescrCntDay}</tpl>'+
        '</span>';

    var iconPositionTpl = '<tpl if="this.isUnExec(PrescrFailureType_id)">-151px</tpl>'+ //не выполнено
		'<tpl if="this.isExec(EvnPrescr_IsExec)">-105px</tpl>'+
        '<tpl if="this.isProsrok(EvnPrescr_IsExec, EvnPrescr_setDate, PrescrFailureType_id)">-22px</tpl>'+ //просрочено
        '<tpl if="this.isWork(EvnPrescr_IsExec, EvnPrescr_setDate, PrescrFailureType_id)">0</tpl>';
    var execDayTpl = '<span style="width:16px; height:16px; '+
        'background:url(/img/EvnPrescrPlan/icon.png) no-repeat left top; '+
        'background-position:0 '+ iconPositionTpl +'; '+
        'display: block; position: relative; top: 0; left: 20px;"></span>';
    var menuBtnTpl = '<span class="EvnPrescrTreatMenuBtn" '+
        'onclick="Ext.getCmp(\''+thas.id+'\').openEvnPrescrTreatActionMenu(event, \'{EvnPrescrTreatDrug_id}\');"></span>';

    var rowTpl = '<tpl for="."><tr style="'+ trDrugStyle +'" class="DrugRow" id="DrugRow{EvnPrescrTreatDrug_id}"'+
        gTipTpl + onRowEventsTpl +'>'+
        '<td style="'+ tdDayNumStyle +'"'+ onCellEventsTpl +'>'+ dayNumTpl +'</td>'+
        '<td style="'+ tdDrugNameStyle +'"'+ onCellEventsTpl +' class="DrugCell{Drug_key}">'+ drugNameTpl +'</td>'+
        '<td style="'+ tdDefaultStyle +'"'+ onCellEventsTpl +' class="DrugCell{Drug_key}">'+ doseDayTpl +'</td>'+
        '<td style="'+ tdDefaultStyle +'"'+ onCellEventsTpl +' class="DrugCell{Drug_key}">'+ doseOneTpl +'</td>'+
        '<td style="'+ tdDefaultStyle +'"'+ onCellEventsTpl +' class="DrugCell{Drug_key}">'+ cntDayTpl +'</td>'+
        '<td style="'+ tdDefaultStyle +'"'+ onCellEventsTpl +' class="DrugCell{Drug_key}">'+ execDayTpl +'</td>'+
        '<td style="'+ tdDefaultStyle +'" class="DrugCell{Drug_key}">'+ menuBtnTpl +'</td>'+
        '</tr></tpl>';

    var tableTpl = '<table><thead><tr>'+
        '<th style="'+ thStyle +'">'+langs('День')+'</th>'+
        '<th style="'+ thStyle +'">'+langs('Медикамент')+'</th>'+
        '<th style="'+ thStyle +'">'+langs('Суточная доза')+'</th>'+
        '<th style="'+ thStyle +'">'+langs('Разовая доза')+'</th>'+
        '<th style="'+ thStyle +'">'+langs('Приемов в день')+'</th>'+
        '<th style="'+ thStyle +'">'+langs('Выполнение')+'</th>'+
        '<th style="'+ thStyle +'">'+langs('Действие')+'</th>'+
        '</tr></thead><tbody class="EvnCourseTreatItems">'+
        rowTpl+
        '</tbody></table>';

    thas.dataViewCfg = {
        id: thas.id,
        store: new Ext.data.Store({
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                id: 'EvnPrescrTreatDrug_id'
            }, [
                {name: 'EvnPrescrTreatDrug_id', mapping: 'EvnPrescrTreatDrug_id',key:true},
                {name: 'Drug_key' ,mapping:'Drug_key'},
                {name: 'dayNum', mapping: 'dayNum'},
                {name: 'Drug_Name', mapping: 'Drug_Name'},
                {name: 'DrugTorg_Name', mapping: 'DrugTorg_Name'},
                {name: 'KolvoEd', mapping: 'KolvoEd'},
                {name: 'DrugForm_Nick', mapping: 'DrugForm_Nick'},
                {name: 'Kolvo', mapping: 'Kolvo'},
                {name: 'EdUnits_Nick', mapping: 'EdUnits_Nick'},
                {name: 'DoseDay', mapping: 'DoseDay'},
                {name: 'PrescrCntDay', mapping: 'PrescrCntDay'},
                {name: 'FactCntDay', mapping: 'FactCntDay'},
                {name: 'EvnCourse_id' ,mapping:'EvnCourse_id'},
                {name: 'EvnPrescr_id', mapping: 'EvnPrescr_id'},
                {name: 'EvnPrescr_pid', mapping: 'EvnPrescr_pid'},
                {name: 'EvnPrescr_rid', mapping: 'EvnPrescr_rid'},
                {name: 'PrescriptionType_id', mapping: 'PrescriptionType_id'},
                {name: 'PrescriptionType_Code', mapping: 'PrescriptionType_Code'},
                {name: 'EvnPrescr_setDate', mapping: 'EvnPrescr_setDate', dateFormat: 'd.m.Y', type: 'date'},
                {name: 'EvnPrescr_IsHasEvn', mapping: 'EvnPrescr_IsHasEvn'},
                {name: 'PrescriptionStatusType_id', mapping: 'PrescriptionStatusType_id'},
                {name: 'PrescrFailureType_id', mapping: 'PrescrFailureType_id'},
                {name: 'EvnPrescr_IsExec', mapping: 'EvnPrescr_IsExec'}
            ]),
            url:'/?c=EvnPrescr&m=loadEvnPrescrTreatDrugDataView'
        }),
        itemSelector: 'tr',
        autoHeight: true,
        tpl : new Ext.XTemplate(
            tableTpl,
            {
                isExec: function(EvnPrescr_IsExec){
                    return (EvnPrescr_IsExec == 2);
                },
                isProsrok: function(EvnPrescr_IsExec, setDate, PrescrFailureType_id){
                    return Ext.isEmpty(PrescrFailureType_id) && (EvnPrescr_IsExec != 2 && setDate.format('Y-m-d') < thas.curDate.format('Y-m-d'));
                },
				isUnExec: function(PrescrFailureType_id){
                    return !Ext.isEmpty(PrescrFailureType_id);
                },
                isWork: function(EvnPrescr_IsExec, setDate, PrescrFailureType_id){
                    return Ext.isEmpty(PrescrFailureType_id) && (EvnPrescr_IsExec != 2 && setDate.format('Y-m-d') >= thas.curDate.format('Y-m-d'));
                },
                isCurDate: function(setDate){
                    return (setDate.format('Y-m-d') == thas.curDate.format('Y-m-d'));
                }
            }
        ),
        openEvnPrescrTreatActionMenu: function(e, key) {
            var rec = this.getStore().getById(key);
            var elDrugRow = Ext.get('DrugRow'+ key);
            if ( !rec || !elDrugRow ) {
                return false;
            }
            var evnsysnick = thas.emk.defineParentEvnClass().EvnClass_SysNick,
                coords = [e.clientX, e.clientY],
                PrescrActions = {},
                data = rec.data;
            var evdata = thas.emk.getObjectData(evnsysnick, data.EvnPrescr_pid);
            if ( evdata == false) {
                return false;
            }
			data.Diag_id = evdata.Diag_id;
            var allowActions = (evdata.accessType && data.EvnPrescr_id && data.EvnPrescr_id > 0);
            var allowActionEdit = (allowActions && evdata.accessType != 'view' && data.EvnPrescr_IsExec != 2 && data.PrescriptionStatusType_id == 1);
            var allowActionExec = (allowActions && evdata.accessType != 'view' && sw.Promed.EvnPrescr.isExecutable(sw.Promed.EvnPrescr._createExecEvnPrescrParams(data, thas.emk)));
            var allowActionUnExec = (allowActions && evdata.accessType != 'view' && sw.Promed.EvnPrescr.isUnExecutable(sw.Promed.EvnPrescr._createUnExecEvnPrescrParams(data, thas.emk)));
            var actions = [
                {
                    name:'action_exec',
                    text:langs('Выполнить'),
                    tooltip: langs('Выполнить'),
                    disabled:  !(allowActionExec && (getRegionNick() != 'vologda' || (getRegionNick() == 'vologda' && Ext.isEmpty(rec.get('PrescrFailureType_id'))))),
                    handler: function(item, evn) {
                        var conf = sw.Promed.EvnPrescr._createExecEvnPrescrParams(data, thas.emk);
                        conf.onExecSuccess = function(cnfg){
                            thas.dataView.reLoad(cnfg);
                        };
						sw.Promed.EvnPrescr._execEvnPrescr(conf, data, thas.elData.object, evn.getXY());
                    }
                },
                {
                    name:'action_unexec',
                    text:langs('Отменить выполнение'),
                    tooltip: langs('Отменить выполнение'),
                    disabled: !allowActionUnExec,
                    handler: function() {
                        var conf = sw.Promed.EvnPrescr._createUnExecEvnPrescrParams(data, thas.emk);
                        conf.onSuccess = function() {
                            thas.dataView.reLoad();
                        };
                        sw.Promed.EvnPrescr.unExec(conf);
                    }
                },
                {
                    name:'action_edit',
                    text:langs('Редактировать'),
                    tooltip: langs('Редактировать назначение'),
                    disabled: ( !allowActionEdit ),
                    handler: function() {
						sw.Promed.EvnPrescr._openEvnPrescrEditWindow(data, evnsysnick, thas.emk, function(){
                            thas.dataView.reLoad();
                        });
                    }
                },
                {
                    name:'action_delete',
                    text:langs('Отменить назначение'),
                    disabled: ( !allowActions || evdata.accessType == 'view' || data.EvnPrescr_IsExec == 2),
                    handler: function() {
                        sw.Promed.EvnPrescr.cancel({
                            ownerWindow: thas.emk
                            ,getParams: function(){
                                return {
                                    parentEvnClass_SysNick: evnsysnick
                                    ,PrescriptionType_id: data.PrescriptionType_id
                                    ,EvnPrescr_id: data.EvnPrescr_id
                                };
                            }
                            ,callback: function(){
                                thas.dataView.reLoad();
                            }
                        });
                    }
                },
                {
                    name:'action_unexec_reason',
                    text:langs('Причина невыполнения'),
					disabled: ( !allowActions || evdata.accessType == 'view' || data.EvnPrescr_IsExec == 2),
                    hidden: getRegionNick() != 'vologda',
                    handler: function() {
						getWnd('swEvnPrescrUnExecReasonWindow').show({
							callback: function() {
								thas.dataView.reLoad();
							},
							EvnPrescr_id: rec.get('EvnPrescr_id'),
							PrescrFailureType_id: rec.get('PrescrFailureType_id'),
							onHide: function() {

							}
						});
						return true;
                    }
                },
                {
                    name:'action_delete_unexec_reason',
                    text:langs('Удалить причину невыполнения'),
					disabled: ( !allowActions || evdata.accessType == 'view' || Ext.isEmpty(rec.get('PrescrFailureType_id'))),
					hidden: getRegionNick() != 'vologda',
                    handler: function() {
						that = this;
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									thas.emk.getLoadMask(langs('Удаление причины невыполнения')).show();
									Ext.Ajax.request({
										callback: function(options, success, response) {
											thas.emk.getLoadMask().hide();
											thas.dataView.reLoad();
										}.createDelegate(that),
										params: {
											EvnPrescr_id: rec.get('EvnPrescr_id')
										},
										url: '/?c=EvnPrescr&m=saveEvnPrescrUnExecReason'
									});
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: 'Удалить причину невыполнения?',
							title: langs('Вопрос')
						});
						return true;
                    }
                }];

            var actionNameList = ['action_exec','action_unexec','action_edit',
                'action_delete','action_unexec_reason','action_delete_unexec_reason'];

            for (var i=0; i<actions.length; i++) {
                if (actions[i]['name'].inlist(actionNameList)) {
                    PrescrActions[actions[i]['name']] = new Ext.Action( {
                        id: 'id_'+actions[i]['name'],
                        text: actions[i]['text'],
                        disabled: actions[i]['disabled'] || false,
                        hidden: actions[i]['hidden'] || false,
                        tooltip: actions[i]['tooltip'],
                        iconCls : actions[i]['iconCls'] || 'x-btn-text',
                        icon: actions[i]['icon'] || null,
                        menu: actions[i]['menu'] || null,
                        scope: this,
                        handler: actions[i]['handler']
                    });
                }
            }

            this.PrescrListActionMenu = new Ext.menu.Menu();
            for (var key in PrescrActions) {
                if (PrescrActions.hasOwnProperty(key)) {
                    this.PrescrListActionMenu.add(PrescrActions[key]);
                }
            }

            var Drug_key = rec.get('Drug_key');

			this.PrescrListActionMenu.on('beforehide',function(){
				//log('PrescrListActionMenu beforehide', elDrugRow.id, Drug_key);
				elDrugRow.removeClass('openedEvnPrescrTreatActionMenu');
				thas.dataView.unSelectDrugRow(elDrugRow.id);
			});

			this.PrescrListActionMenu.showAt(coords);
			//log('PrescrListActionMenu showAt', elDrugRow.id, Drug_key);
			elDrugRow.addClass('openedEvnPrescrTreatActionMenu');
			thas.dataView.selectDrugRow(elDrugRow.id);
			return true;
        },
        reLoad: function(cnfg){
            thas.emk.getLoadMask(langs('Обновление списка назначений')).show();
            var cmp = this;
            var store = this.getStore();
            var Drug_key = this.filtered||null;
            store.removeAll();
            store.load({
                callback: function() {
                    thas.emk.getLoadMask().hide();
                    cmp.setDayNum();
                    if (Drug_key) {
                        cmp.toogleDrug(Drug_key, true);
                    }
                }
            });
			if (cnfg && cnfg.mode && 'withUseDrug' == cnfg.mode && cnfg.EvnPrescr_pid) {
				//рефреш раздела Использование медикаментов при списании по назначению
				thas.emk.reloadViewForm({
					section_code: 'EvnDrug',
					object_key: 'EvnDrug_id',
					object_value: 111222333,
					parent_object_key: 'EvnDrug_pid',
					parent_object_value: cnfg.EvnPrescr_pid,
					section_id: 'EvnDrugList_'+ cnfg.EvnPrescr_pid
				});
			}
        },
        setDayNum: function(){
            //EvnPrescr_setDate ([setDate.format('Y-m-d'),thas.begDate.format('Y-m-d')]);
            var getDateDiff = function(begDate, setDate) {
                var diffTime = setDate.getTime()-begDate.getTime();
                var dayMs = (1000*60*60*24);
                diffTime = diffTime+dayMs;
                var days = diffTime/dayMs;
                return Math.round(days);
            };
            this.getStore().each(function(rec) {
                rec.set('dayNum', getDateDiff(thas.begDate, rec.get('EvnPrescr_setDate')));
                rec.commit();
                return true;
            });
            this.refresh();
        },
        eachDrugTdList: function(Drug_key, func){
            var id = this.initialConfig.renderTo;
            var selector = 'td[class*=DrugCell'+ Drug_key +']';
            var node_list = Ext.query(selector, Ext.getDom(id));
            var i, el;
            for(i=0; i < node_list.length; i++) {
                el = new Ext.Element(node_list[i]);
                if (el) func(el);
            }
        },
        selectDrug: function(Drug_key){
            this.eachDrugTdList(Drug_key, function(el) {
                el.setStyle('background', '#d9e8fb');
            });
        },
        unSelectDrug: function(Drug_key){
            //log('unSelectDrug none');
            this.eachDrugTdList(Drug_key, function(el) {
                var parent = el.parent('tr[class*=DrugRow]', false);
                if (parent && !parent.hasClass('openedEvnPrescrTreatActionMenu')) {
                    el.setStyle('background', 'none');
                }
            });
        },
		selectDrugRow: function(id){
			var el = Ext.get(id);
			//log('selectDrugRow', id, el);
			if (el) {
				el.setStyle('background', '#d9e8fb');
			}
		},
		unSelectDrugRow: function(id){
			var el = Ext.get(id);
			//log('unSelectDrugRow', id, el);
			if (el && !el.hasClass('openedEvnPrescrTreatActionMenu')) {
				el.setStyle('background', 'none');
			}
		},
        toogleDrug: function(Drug_key, afterReload){
            var text;
            if (this.filtered && !afterReload) {
                this.getStore().filterBy(function () {
                    return true;
                });
                this.filtered = null;
                text = langs('Отфильтровать по этому элементу');
            } else {
                this.getStore().filterBy(function (rec) {
                    return rec.get('Drug_key') == Drug_key;
                });
                this.filtered = Drug_key;
                text = langs('Сбросить фильтр');
            }
            this.eachDrugTdList(Drug_key, function(el) {
                var parent = el.parent('tr[class*=DrugRow]', false);
                if (parent) parent.set({title: text});
            });
        }
    };

    this.render = function(renderTo) {
        if (!thas.dataView) {
            thas.dataViewCfg.renderTo = renderTo;
            thas.dataView = new Ext.DataView(thas.dataViewCfg);
        }
    };

    if (renderTo) {
        this.render(renderTo);
    }
    if (this.validateEvnCourseData(data)) {
        this.applyEvnCourseData(data);
    }
    return this;
};

/**
 * Вспомогательный объект для СМП
 */
sw.Promed.CmpCallCardHelper = {
	Report: {
		getPrintMenu: function(Person_id, data) {
			var me = this;
			if (me.printMenu) {
				me.printMenu.destroy();
				me.printMenu = null;
			}
			me.printMenu = new Ext.menu.Menu();
			me.printMenu.add({
				text: langs('Карта СМП'),
				iconCls: 'print16',
				handler: function() {
					window.open('/?c=CmpCallCard&m=printCmpCloseCard110&CmpCallCard_id=' + data.CmpCallCard_id, '_blank');
				}
			});
			me.printMenu.add({
				text: langs('Справка о стоимости лечения'),
				iconCls: 'print16',
				hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
				handler: function(){
					getWnd('swCostPrintWindow').show({
						CmpCallCard_id: data.CmpCallCard_id,
						type: 'CmpCallCard',
						callback: function() {
							if (data.callbackCostPrint && typeof data.callbackCostPrint == 'function') {
								data.callbackCostPrint();
							}
						}
					});
				}
			});
			return me.printMenu;
		},
		showPrintMenu: function(Person_id, data, btnEl) {
			var me = this,
				menu = me.getPrintMenu(Person_id, data);
			menu.show(btnEl);
		}
	}
}

/**
 * Вспомогательный объект для КВС
 */
sw.Promed.EvnPSHelper = {
	Report: {
		getPrintMenu: function(Person_id, data) {
			var me = this;
			if (me.printMenu) {
				me.printMenu.destroy();
				me.printMenu = null;
			}
			me.printMenu = new Ext.menu.Menu();
			me.printMenu.add({
				text: langs('Карта выбывшего из стационара'),
				iconCls: 'print16',
				handler: function() {
					var params = {};
					params.Parent_Code = 4;
					switch (getRegionNick()){
						case 'kz':
							params.EvnPS_id = data.EvnPS_id;
							params.LpuUnitType_SysNick = data.LpuUnitType_SysNick;
							break;
						default:
							params.EvnPS_id = data.EvnPS_id;
							break;
					}
					printEvnPS(params);
				}
			});
			me.printMenu.add({
				text: langs('Справка о стоимости лечения'),
				iconCls: 'print16',
				hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
				handler: function(){
					sw.Promed.CostPrint.print({
						Evn_id: data.EvnPS_id,
						type: 'EvnPS',
						callback: function() {
							if (data.callbackCostPrint && typeof data.callbackCostPrint == 'function') {
								data.callbackCostPrint();
							}
						}
					});
				}
			});
			me.printMenu.add({
				text: langs('Расчет КСГ'),
				iconCls: 'print16',
				hidden: !(getRegionNick().inlist(['ufa']) && data.LeaveType_Code && data.LeaveType_Code.inlist([1,2,3,4])),
				handler: function() {
					printBirt({
						'Report_FileName': 'Raschet_KSG.rptdesign',
						'Report_Params': '&paramEvnPS=' + data.EvnPS_id,
						'Report_Format': 'html'
					});
				}
			});
			me.printMenu.add({
				text: langs('Карта внутреннего контроля качества и безопасности медицинской деятельности'),
				iconCls: 'print16',
				hidden: !(getRegionNick().inlist(['kareliya'])),
				handler: function() {
					printBirt({
						'Report_FileName': 'FormaKBK_EvnPS.rptdesign',
						'Report_Params': '&paramEvnPS=' + data.EvnPS_id,
						'Report_Format': 'doc'
					});
				}
			});
			me.printMenu.add({
				text: langs('Справка о фактической себестоимости'),
				iconCls: 'print16',
				hidden: !(getRegionNick().inlist(['kz'])),
				handler: function() {
					printBirt({
						'Report_FileName': 'hosp_Spravka_KSG.rptdesign',
						'Report_Params': '&paramEvnPS=' + data.EvnPS_id,
						'Report_Format': 'pdf'
					});
				}
			});
			/*me.printMenu.add({
				text: langs('Печать шкалы Рэнкина'),
				iconCls: 'print16',
				hidden: !(getRegionNick().inlist(['adygeya']) && data.DiagFinance_IsRankin == 2),
				handler: function() {
					printRankinScale(data.EvnPS_id);
				}
			});*/
			return me.printMenu;
		},
		showPrintMenu: function(Person_id, data, btnEl) {
			var me = this,
				menu = me.getPrintMenu(Person_id, data);
			menu.show(btnEl);
		}
	}
}

/**
 * Вспомогательный объект для параклинической услуги
 */
sw.Promed.EvnUslugaParHelper = {
	Report: {
		getPrintMenu: function(Person_id, data, printFunc) {
			var me = this;
			if (me.printMenu) {
				me.printMenu.destroy();
				me.printMenu = null;
			}
			me.printMenu = new Ext.menu.Menu();
			me.printMenu.add({
				text: langs('Печать'),
				iconCls: 'print16',
				handler: function() {
					printFunc();
				}
			});
			me.printMenu.add({
				text: langs('Справка о стоимости лечения'),
				iconCls: 'print16',
				hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
				handler: function(){
					getWnd('swCostPrintWindow').show({
						Evn_id: data.EvnUslugaPar_id,
						type: 'EvnUslugaPar',
						callback: function() {
							if (data.callbackCostPrint && typeof data.callbackCostPrint == 'function') {
								data.callbackCostPrint();
							}
						}
					});
				}
			});
			return me.printMenu;
		},
		showPrintMenu: function(Person_id, data, btnEl, printFunc) {
			var me = this,
				menu = me.getPrintMenu(Person_id, data, printFunc);
			menu.show(btnEl);
		}
	}
}

/**
 * Вспомогательный объект для общей услуги
 */
sw.Promed.EvnUslugaCommonHelper = {
	Report: {
		getPrintMenu: function(Person_id, data, printFunc) {
			var me = this;
			if (me.printMenu) {
				me.printMenu.destroy();
				me.printMenu = null;
			}
			me.printMenu = new Ext.menu.Menu();
			me.printMenu.add({
				text: langs('Печать'),
				iconCls: 'print16',
				handler: function() {
					printFunc();
				}
			});
			me.printMenu.add({
				text: langs('Справка о стоимости лечения'),
				iconCls: 'print16',
				hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
				handler: function(){
					getWnd('swCostPrintWindow').show({
						Evn_id: data.EvnUslugaCommon_id,
						type: 'EvnUslugaCommon',
						callback: function() {
							if (data.callbackCostPrint && typeof data.callbackCostPrint == 'function') {
								data.callbackCostPrint();
							}
						}
					});
				}
			});
			return me.printMenu;
		},
		showPrintMenu: function(Person_id, data, btnEl, printFunc) {
			var me = this,
				menu = me.getPrintMenu(Person_id, data, printFunc);
			menu.show(btnEl);
		}
	}
}

/**
 * Вспомогательный объект для регистра онкологии (Специфика)
 */
sw.Promed.PersonMorbusOnkoHelper = {
	Report: {
		getPrintMenu: function(Person_id, data) {
			var me = this;
			if (me.printMenu) {
				me.printMenu.destroy();
				me.printMenu = null;
			}
			me.printMenu = new Ext.menu.Menu();
			/*me.printMenu.add({
				text: 'Печать',
				iconCls: 'print16',
				hidden: Ext.isEmpty(data.section),
				handler: function() {
					data.win.printHtml(data.section);
				}
			});*/
			me.printMenu.add({
				text: 'Печать в формате «№ 030-ГРР»',
				iconCls: 'print16',
				disabled: Ext.isEmpty(data.Morbus_id),
				hidden: (getRegionNick() == 'kz'),
				handler: function() {
					printBirt({
						'Report_FileName': 'f030grr.rptdesign',
						'Report_Params': '&paramMorbus=' + data.Morbus_id,
						'Report_Format': 'pdf'
					});
				}
			});
			me.printMenu.add({
				text: 'Печать в формате «№ 027-1/У»',
				iconCls: 'print16',
				sectionCode: 'MorbusOnkoLeave',
				disabled: Ext.isEmpty(data.MorbusOnkoLeave_id),
				hidden: (getRegionNick() == 'kz'),
				handler: function(e, c, d) {
					data.win.printHtml({
						archiveRecord: 0,
						object:	'MorbusOnkoLeave',
						object_id: 'MorbusOnkoLeave_id',
						object_value: data.MorbusOnkoLeave_id
					});
				}
			});
			me.printMenu.add({
				text: 'Печать в формате «№ 027-2/У»',
				iconCls: 'print16',
				disabled: Ext.isEmpty(data.EvnOnkoNotifyNeglected_id),
				hidden: (getRegionNick() == 'kz'),
				handler: function() {
					printBirt({
						'Report_FileName': 'OnkoNotifyNeglected.rptdesign',
						'Report_Params': '&paramEvnOnkoNotifyNeglected=' + data.EvnOnkoNotifyNeglected_id,
						'Report_Format': 'pdf'
					});
				}
			});
			me.printMenu.add({
				text: 'Печать в формате «№ 030-6/ТД»',
				iconCls: 'print16',
				sectionCode: 'MorbusOnkoVizitPLDop',
				disabled: Ext.isEmpty(data.MorbusOnkoVizitPLDop_id),
				hidden: (getRegionNick() == 'kz'),
				handler: function(e, c, d) {
					data.win.printHtml({
						archiveRecord: 0,
						object:	'MorbusOnkoVizitPLDop',
						object_id: 'MorbusOnkoVizitPLDop_id',
						object_value: data.MorbusOnkoVizitPLDop_id
					});
				}
			});
			me.printMenu.add({
				text: 'Печать в формате «№ 030-6/У»',
				iconCls: 'print16',
				disabled: Ext.isEmpty(data.Morbus_id),
				handler: function() {
					printBirt({
						'Report_FileName': 'f030_6u_onko.rptdesign',
						'Report_Params': '&paramMorbus=' + data.Morbus_id,
						'Report_Format': 'pdf'
					});
				}
			});
			return me.printMenu;
		},
		showPrintMenu: function(Person_id, data, btnEl) {
			var me = this,
				menu = me.getPrintMenu(Person_id, data);
			menu.show(btnEl);
		}
	}
};


/**
 * Вспомогательный объект для ТАП
 */
sw.Promed.EvnPLHelper = {
	Report: {
		getPrintMenu: function(Person_id, data) {
			var me = this;
			if (me.printMenu) {
				me.printMenu.destroy();
				me.printMenu = null;
			}
			me.printMenu = new Ext.menu.Menu();
			me.printMenu.add({
				text: langs('Талон амбулаторного пациента'),
				iconCls: 'print16',
				handler: function() {
					printEvnPL({
						type: 'EvnPL',
						EvnPL_id: data.EvnPL_id
					});
				}
			});
			me.printMenu.add({
				text: langs('Справка о стоимости лечения'),
				iconCls: 'print16',
				hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
				handler: function(){
					sw.Promed.CostPrint.print({
						Evn_id: data.EvnPL_id,
						type: 'EvnPL',
						callback: function() {
							if (data.callbackCostPrint && typeof data.callbackCostPrint == 'function') {
								data.callbackCostPrint();
							}
						}
					});
				}
			});
			me.printMenu.add({
				text: langs('Карта внутреннего контроля качества и безопасности медицинской деятельности'),
				iconCls: 'print16',
				hidden: !(getRegionNick().inlist(['kareliya'])),
				handler: function() {
					printBirt({
						'Report_FileName': 'FormaKBK_EvnPL.rptdesign',
						'Report_Params': '&paramEvnPL=' + data.EvnPL_id,
						'Report_Format': 'doc'
					});
				}
			});
			return me.printMenu;
		},
		showPrintMenu: function(Person_id, data, btnEl) {
			var me = this,
				menu = me.getPrintMenu(Person_id, data);
			menu.show(btnEl);
		}
	}
}

/**
 * Вспомогательный объект со статическими данными по стоматологии
 */
sw.Promed.StomHelper = {
    USLUGA_PARODONTOGRAM_ATTR: 'parondontogram',
    /**
     * Запрос последнего стоматологического посещения
     * @param {integer} Person_id
     * @param {function} callback
     */
    loadLastEvnPLStomData: function(Person_id, callback) {
        Ext.Ajax.request({
            params: {
                Person_id: Person_id
            },
            url: '/?c=EvnVizit&m=loadLastEvnPLStomData',
            success: function(response) {
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if (response_obj.success) {
                    callback(response_obj);
                }
            }
        });
    },
    Report: {
        getPrintMenu: function(getData, options) {
			if ( typeof options != 'object' ) {
				options = new Object();
			}
            var me = this;
            if (me.printMenu) {
                me.printMenu.destroy();
                me.printMenu = null;
            }
            if (options.isExt6) {
				me.printMenu = Ext6.create('Ext6.menu.Menu');
			} else {
				me.printMenu = new Ext.menu.Menu();
			}
            me.printMenu.add({
                text: langs('Талон амбулаторного пациента'),
                iconCls: 'print16',
                handler: function() {
                    getData(function(Person_id, data) {
						if ( typeof data != 'object' || Ext.isEmpty(data.EvnPLStom_id) ) {
							sw.swMsg.alert(langs('Ошибка'), langs('Пациент не имеет стоматологических посещений'));
						}
						else {
							printEvnPL({
								type: 'EvnPLStom',
								EvnPL_id: data.EvnPLStom_id
							});
						}
                    });
                }
            });
			if (!options.hideCostPrint) {
				me.printMenu.add({
					text: langs('Справка о стоимости лечения'),
					iconCls: 'print16',
					hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
					handler: function () {
						getData(function (Person_id, data) {
							if (typeof data != 'object' || Ext.isEmpty(data.EvnPLStom_id)) {
								sw.swMsg.alert(langs('Ошибка'), langs('Пациент не имеет стоматологических посещений'));
							}
							else {
								sw.Promed.CostPrint.print({
									Evn_id: data.EvnPLStom_id,
									type: 'EvnPLStom',
									callback: function () {
										if (data.callbackCostPrint && typeof data.callbackCostPrint == 'function') {
											data.callbackCostPrint();
										}
									}
								});
							}
						});
					}
				});
			}
            me.printMenu.add({
                text: langs('Форма 043/у'),
                iconCls: 'print16',
                handler: function() {
                    getData(function(Person_id, data) {
						printBirt({
							'Report_FileName': 'f043u.rptdesign',
							'Report_Params': '&paramLpu=' + (!Ext.isEmpty(getGlobalOptions().lpu_id)?getGlobalOptions().lpu_id:0) + '&paramEvnVizitPLStom_id=' + (!Ext.isEmpty(data.EvnVizitPLStom_id) ? data.EvnVizitPLStom_id : 0) + '&paramPerson_id=' + Person_id,
							'Report_Format': 'pdf'
						});
                    });
                }
            });
            me.printMenu.add({
                text: langs('Вкладыш к форме 043/у'),
                iconCls: 'print16',
                handler: function() {
                    getData(function(Person_id, data) {
						printBirt({
							'Report_FileName': 'f043u_insert.rptdesign',
							'Report_Params': '&paramLpu=' + (!Ext.isEmpty(getGlobalOptions().lpu_id)?getGlobalOptions().lpu_id:0) + '&paramEvnVizitPLStom_id=' + (!Ext.isEmpty(data.EvnVizitPLStom_id) ? data.EvnVizitPLStom_id : 0) + '&paramPerson_id=' + Person_id,
							'Report_Format': 'pdf'
						});
                    });
                }
            });
            if (getRegionNick()=='pskov') {
                me.printMenu.add({
                    text: langs('Печать согласия на мед.вмешательство'),
                    iconCls: 'print16',
                    handler: function() {
                        getData(function(Person_id, data){
							printBirt({
								'Report_FileName': 'Person_soglasie.rptdesign',
								'Report_Params': '&paramPerson=' + Person_id,
								'Report_Format': 'pdf'
							});
                        });
                    }
                });
            }
            return me.printMenu;
        },
        showPrintMenu: function(Person_id, data, btnEl) {
            var me = this,
                menu = me.getPrintMenu(function(callback){
                    callback(Person_id, data);
                });
            menu.show(btnEl);
        }
    },
    ToothStateValues: {
        _store: null,
        getStoreReader: function()
        {
            return new Ext.data.JsonReader({
                id: 'ToothStateValues_id'
            }, [
                {name: 'Tooth_id', mapping: 'Tooth_id'},
                {name: 'JawPartType_Code', mapping: 'JawPartType_Code', type: 'int'},
                {name: 'Tooth_Code', mapping: 'Tooth_Code', type: 'int'},
                {name: 'ToothStateType_Code', mapping: 'ToothStateType_Code', type: 'int'},
                {name: 'ToothStateType_id', mapping: 'ToothStateType_id'},
                {name: 'ToothStateType_Name', mapping: 'ToothStateType_Name'},
                {name: 'ToothStateType_Nick', mapping: 'ToothStateType_Nick'},
                {name: 'ToothStateValues_id', mapping: 'ToothStateValues_id'},
                {name: 'ToothStateType_Value', mapping: 'ToothStateType_Value', type: 'float'}
            ]);
        },
        loadStore: function(store, callback)
        {
            var me = this;
            if (!me._store) {
                me._store = new Ext.data.Store({
                    autoLoad: false,
                    reader: me.getStoreReader(),
                    url: '/?c=Parodontogram&m=doLoadToothStateValues'
                });
            }
            if (me._store.getCount()>0) {
                store.loadData(getStoreRecords(me._store));
                callback(store);
            } else {
                me._store.load({
                    callback: function() {
                        store.loadData(getStoreRecords(me._store));
                        callback(store);
                    }
                });
            }
        }
    },
    ToothMap: {
        _toothStore: null,
        /**
         * Справочник зубов
         * @param callback
         */
        loadToothStore: function(callback)
        {
            if (typeof callback != 'function') {
                callback = Ext.emptyFn;
            }
            var me = this;
            if (!me._toothStore) {
                me._toothStore = new Ext.db.AdapterStore({
                    autoLoad: true,
                    dbFile: 'Promed.db',
                    fields: [
                        {name: 'Tooth_id', mapping: 'Tooth_id'},
                        {name: 'JawPartType_id', mapping: 'JawPartType_id', type: 'int'},
                        {name: 'Tooth_Code', mapping: 'Tooth_Code', type: 'int'},
                        {name: 'Tooth_ACode', mapping: 'Tooth_ACode'},
                        {name: 'Tooth_Name', mapping: 'Tooth_Name'}
                    ],
                    key: 'Tooth_id',
                    listeners: {
                        load: function(store) {
                            callback(store);
                        }
                    },
                    sortInfo: {
                        field: 'Tooth_Code'
                    },
                    tableName: 'Tooth'
                });
            } else {
                callback(me._toothStore);
            }
        },
        isToothHasFiveSegments: function(sysNum) {
            return (sysNum > 3);
        },
        getToothSysNum: function(code) {
            return code.toString().charAt(1);
        },
        getToothCodeById: function(val) {
            var value = null;
            if (val) {
                this._toothStore.each(function(rec) {
                    if (rec.get('Tooth_id') == val) {
                        value = rec.get('Tooth_Code');
                    }
                    return !value;
                });
            }
            return value;
        },
        getToothIdByCode: function(code) {
            var value = null;
            if (!code) {
                return value;
            }
            this._toothStore.each(function(rec) {
                if (rec.get('Tooth_Code') == code) {
                    value = rec.get('Tooth_id');
                }
                return !value;
            });
            return value;
        },
        hasToothCode: function(code) {
            var me = this,
                value = false;
            me._toothStore.each(function(rec) {
                if (rec.get('Tooth_Code') == code) {
                    value = true;
                }
                return !value;
            });
            return value;
        },
        getVisibleSurfaceList: function(code) {
            if (!code || code < 11) {
                return [];
            }
            var sysNum = this.getToothSysNum(code);
            if (this.isToothHasFiveSegments(sysNum)) {
                return ['1','2','3','4','5'];
            } else {
                return ['1','2','3','4'];
            }
        },
        getDefaultToothType: function(Person_Age, sysNum) {
            var id;//ToothStateClass_id
            switch(true) {
                case (Person_Age < 5): id = 13; break;
                case (Person_Age > 14): id = 12; break;
                case (sysNum > 5): id = 12; break;
                default: id = 13; break;
            }
            return id;
        },
        hasType: function(states, id) {
            var res, i, state;
            for(i=0; i < states.length; i++) {
                state = states[i];
                if (!state['ToothStateClass_id'].toString().inlist(['12','15','13','14'])) {
                    continue;
                }
                if (id == state['ToothStateClass_id']) {
                    res = state;
                }
            }
            return res;
        },
        hasState: function(states, id, onlySurface) {
            var res, i, state;
            for(i=0; i < states.length; i++) {
                state = states[i];
                if (state['ToothStateClass_id'].toString().inlist(['12','15','13','14'])) {
                    continue;
                }
                if (state['ToothSurfaceType_id'] && !onlySurface) {
                    continue;
                }
                if (!state['ToothSurfaceType_id'] && onlySurface) {
                    continue;
                }
                if (id == state['ToothStateClass_id']) {
                    res = state;
                }
            }
            return res;
        }
    }
};
/**
 * Поле ввода номера зуба
 */
sw.Promed.SwToothField = Ext.extend(Ext.form.NumberField, {
    allowDecimals: false,
    allowNegative: false,
	defaultAutoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
    fieldLabel: langs('Зуб'),
    name: 'Tooth_Code',
    width: 50,
    applyChangeTo: function(win, toothIdField, surfaceGroup, disabled) {
        var me = this,
            showItems = sw.Promed.StomHelper.ToothMap.getVisibleSurfaceList(me.getValue());
        surfaceGroup.items.each(function(item){
            item.disable();
			if ( disabled !== false ) {
				item.setValue(false);
			}
            if (item.value.toString().inlist(showItems)) {
				if (win && win.action && win.action != 'view' && !disabled) {
					item.enable();
				}
            }
        });
        toothIdField.setValue(me.getToothId());
        win.syncSize();
        win.doLayout();
    },
    setToothId: function(val) {
        this.setValue(sw.Promed.StomHelper.ToothMap.getToothCodeById(val));
    },
    getToothId: function() {
        return sw.Promed.StomHelper.ToothMap.getToothIdByCode(this.getValue());
    },
    hasCode: function(code) {
        return sw.Promed.StomHelper.ToothMap.hasToothCode(code);
    },
    validator: function(value) {
        if (Ext.isEmpty(value) && this.allowBlank) {
            return true;
        }
        if ( sw.Promed.StomHelper.ToothMap.hasToothCode(value) ) {
            return true;
        }
        return langs('Значение поля должно быть из диапазонов 11-18, 21-28, 31-38, 41-48, 51-55, 61-65, 71-75, 81-85');
    },
    initComponent: function() {
        sw.Promed.StomHelper.ToothMap.loadToothStore();
        sw.Promed.SwToothField.superclass.initComponent.apply(this, arguments);
    }
});
Ext.reg('swtoothfield', sw.Promed.SwToothField);

/**
 * Панель просмотра/редактирования пародонтограммы
 * @package      libs
 * @access       public
 * @autor		 Alexander Permyakov
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @version      05.2014
 */
sw.Promed.ParodontogramPanel = Ext.extend(sw.Promed.Panel, {
    collapsible: false,
    height: 185,
    layout: 'border',
    border: false,
    bodyStyle: 'background-color: #fff;',

	hiddenDelBtn: false,
    _params: {
        EvnUslugaStom_id: null,
        EvnUslugaStom_setDate: null,
        Person_id: null
    },
    _enableEdit: false,
    _isReadOnly: true,
    _hasChanges: false,

    applyParams: function(Person_id, EvnUslugaStom_id, EvnUslugaStom_setDate) {
        this.setParam('Person_id', Person_id || null);
        this.setParam('EvnUslugaStom_id', EvnUslugaStom_id || null);
        this.setParam('EvnUslugaStom_setDate', EvnUslugaStom_setDate || null);
    },
    getParam: function(key) {
        return this._params[key] || null;
    },
    setParam: function(key, value) {
        this._params[key] = value || null;
    },
    isNewEvnUslugaStom: function() {
        return this.getParam('EvnUslugaStom_id') == 0;
    },
    isNewParodontogram: function() {
        var me = this,
            flag = true;
        if (me.isNewEvnUslugaStom() || !me.historyStore.getAt(0)) {
            return true;
        }
        me.historyStore.each(function(rec){
            if (rec.get('EvnUslugaStom_id') == me.getParam('EvnUslugaStom_id')) {
                flag = false;
                return false;
            }
            return true;
        });
        return flag;
    },
    isEnableEdit: function() {
        return this._enableEdit||false;
    },
    setEnableEdit: function(enable) {
        if (this.isReadOnly()) {
            this._enableEdit = false;
        } else {
            this._enableEdit = enable;
        }
        var buttons = this.topToolbar.items;
        if (buttons.items && buttons.items[1]) {// del
            buttons.items[1].setDisabled(!this.isAllowDelete());
        }
    },
    getPrintBtn: function() {
        var buttons = this.topToolbar.items;
        if (buttons.items && buttons.items[0]) {
            return buttons.items[0];
        }
        return null;
    },
    isReadOnly: function() {
        return this._isReadOnly;
    },
    setReadOnly: function(isReadOnly) {
        this._isReadOnly = isReadOnly;
        this.setEnableEdit(!isReadOnly);
    },
    doReset: function() {
        this.setParam('Person_id', null);
        this.setParam('EvnUslugaStom_id', null);
        this.setParam('EvnUslugaStom_setDate', null);
        this.doClear(true);
        this.setReadOnly(true);
    },
    doClear: function(isAll) {
        if (isAll) {
            this.historyStore.removeAll();
            this.historyStore.baseParams = {};
        }
        this.mainViewPanelStore.removeAll();
        this.mainViewPanel.refresh();
    },
    _onLoadHistory: function(records, options, success) {
        var me = this;
        if (!success) {
            sw.swMsg.alert(langs('Сообщение'), langs('Неудалось загрузить историю выполнения услуги Пародонтограмма'));
            return false;
        }
        /*log({
            debug: 'onLoadParodontogramHistory',
            panel: me,
            initId: me.getParam('EvnUslugaStom_id')
        });*/
        me.historyComboBox.setValue(me.getParam('EvnUslugaStom_id'));
        me.historyComboBox.fireEvent('change', me.historyComboBox, me.historyComboBox.getValue(), null);
        me.isLoadHistoryByPerson = false;
        me.doLayout();
        return true;
    },
    doLoad: function(byPerson) {
        var me = this;
        /*log({
         debug: 'beforeLoadParodontogramHistory',
         initId: me.getParam('EvnUslugaStom_id'),
         mainStoreCnt: me.mainViewPanelStore.getCount(),
         historyStoreCnt: me.historyStore.getCount()
         });*/
        me.historyStore.baseParams.EvnUslugaStom_id = me.getParam('EvnUslugaStom_id');
        me.historyStore.baseParams.Person_id = me.getParam('Person_id');
        me.isLoadHistoryByPerson = byPerson;
        if (me.historyStore.getCount() == 0) {
            me.historyStore.load({
                params: {
                    Person_id: me.getParam('Person_id')
                },
                scope: me,
                callback: me._onLoadHistory
            });
        } else {
            me._onLoadHistory(null, null, true);
        }
    },
    doPrint: function() {
        //window.open('/?c=Parodontogram&m=doPrint&EvnUslugaStom_id=' + this.getParam('EvnUslugaStom_id'), '_blank');
        var doc = this.mainViewPanel.getParodontogrammaHtml();
        var id_salt = Math.random();
        var win_id = 'printEvent' + Math.floor(id_salt*10000);
        var win = window.open('', win_id);
        win.document.write(doc);
        win.document.close();
        //win.print();
    },
    doDelete: function(options) {
        var me = this;
        if (!me.getParam('EvnUslugaStom_id')) {
            return false;
        }
        if (!options) {
            options = {};
        }
        if (!options.callback) {
            options.callback = function(response_obj){};
        }
        var loadMask = new Ext.LoadMask(me.getEl(), { msg: "Удаление пародонтограммы..." });
        loadMask.show();
        Ext.Ajax.request({
            params: {
                EvnUslugaStom_id: me.getParam('EvnUslugaStom_id')
            },
            url: '/?c=Parodontogram&m=doRemove',
            failure: function() {
                loadMask.hide();
            },
            success: function(response) {
                loadMask.hide();
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if (response_obj.success) {
                    me.doClear(false);// не очищаем историю и базовые параметры
                    me.doLoad(true);// загружаем предыдущие данные
                    options.callback(response_obj);
                }
            }
        });
        return true;
    },
    doLoadViewData: function(EvnUslugaStom_id) {
        var me = this,
            params = {
                Person_id: me.getParam('Person_id')
            };
        if (EvnUslugaStom_id && EvnUslugaStom_id > 0) {
            me._hasChanges = false;
            params.EvnUslugaStom_id = EvnUslugaStom_id;
            me.setEnableEdit(EvnUslugaStom_id == me.getParam('EvnUslugaStom_id'));
        } else {
            me._hasChanges = true;
            me.setEnableEdit(true);
        }
        if (me.getPrintBtn()) {
            me.getPrintBtn().setDisabled(true);
        }
        /*log({
         debug: 'beforeLoadParodontogramViewData',
         initId: me.getParam('EvnUslugaStom_id'),
         post: params
         });*/
        var loadMask = new Ext.LoadMask(me.getEl(), { msg: "Загрузка пародонтограммы..." });
        loadMask.show();
        me.mainViewPanelStore.removeAll();
        me.mainViewPanelStore.load({
            params: params,
            callback: function(){
                loadMask.hide();
                /*log({buttons = this.topToolbar
                 debug: 'onLoadParodontogramViewData',
                 initId: me.getParam('EvnUslugaStom_id'),
                 historyInitId: me.historyStore.baseParams.EvnUslugaStom_id
                 });*/
                me.syncSize();
                me.doLayout();
            }
        });
    },
    showToothStateValuesMenu: function(event, node) {
        var me = this,
            nodeEl = new Ext.Element(node),
            menu = new Ext.menu.Menu({
                //style: 'background-color: #fff; border: 1px solid #8DB2E3;',
                minWidth: 220
            }),
            toothCode = nodeEl.getAttribute('itemId');
        me.toothStateValuesMenuStore.each(function(rec){
            if (rec.get('Tooth_Code') == toothCode) {
                var color = '#000';
                var name = rec.get('ToothStateType_Name');
                var arr = name.split(',');
                if (arr[1]) {
                    name = arr[1];
                }
                if (rec.get('ToothStateType_Code') > 1) {
                    color = '#ff0000';
                }
                menu.addItem(new Ext.menu.Item({
                    hideLabel: true,
                    style: 'text-align: left; cursor: pointer; padding: 0 5px; border: 1px solid #fff;',
                    itemId: rec.get('ToothStateValues_id'),
                    text: '<span style="color: '+ color
                        +'">'+ rec.get('ToothStateType_Nick')
                        +'</span> <span>'+ name +'</span>',
                    handler: function(item){
                        me.onSelectToothStateValue(item.itemId);
                    }
                }));
            }
        });
        nodeEl.setStyle('border', '1px solid #00318B');
        menu.on('hide', function(){
            nodeEl.setStyle('border', '1px solid #808080');
        });
        menu.show(nodeEl);
    },
    onSelectToothStateValue: function(id)
    {
        var me = this;
        var val_rec = me.toothStateValuesMenuStore.getById(id);
        if (!val_rec) {
            return false;
        }
        var toothCode = val_rec.get('Tooth_Code');
        var rec = me.mainViewPanelStore.getAt(0);
        if (!rec) {
            return false;
        }
        var state = rec.get('state');
        if (!state[toothCode]) {
            return false;
        }
        //для сохранения
        state[toothCode]['Tooth_id'] = val_rec.get('Tooth_id');
        state[toothCode]['ToothStateType_id'] = val_rec.get('ToothStateType_id');
        //для отображения
        state[toothCode]['ToothStateType_Code'] = val_rec.get('ToothStateType_Code');
        state[toothCode]['ToothStateType_Nick'] = val_rec.get('ToothStateType_Nick');
        state[toothCode]['ToothState_Value'] = val_rec.get('ToothStateType_Value');
        rec.set('state', state);
        rec.commit();
        me._hasChanges = true;
        me.mainViewPanel.refresh();
        return true;
    },
    isAllowSave: function()
    {
        return (this._hasChanges && this.mainViewPanelStore.getAt(0));
    },
    isAllowDelete: function()
    {
        return (this.isEnableEdit() && this.getParam('EvnUslugaStom_id') > 0);
    },
    doSave: function(options)
    {
        var me = this,
            rec_state = me.mainViewPanelStore.getAt(0),
            toothCode,
            toothId,
            parodontogram_state = {};
        if (!me.getParam('EvnUslugaStom_id')) {
            return false;
        }
        if (!rec_state) {
            return false;
        }
        var state = rec_state.get('state');
        for (toothCode in state) {
            toothId = state[toothCode]['Tooth_id'];
            parodontogram_state[toothId] = state[toothCode]['ToothStateType_id'];
        }
        if (!options) {
            options = {};
        }
        if (!options.callback) {
            options.callback = function(response_obj){};
        }
        var loadMask = new Ext.LoadMask(me.getEl(), { msg: "Сохранение пародонтограммы..." });
        loadMask.show();
        Ext.Ajax.request({
            params: {
                EvnUslugaStom_id: me.getParam('EvnUslugaStom_id'),
                state: Ext.util.JSON.encode(parodontogram_state)
            },
            url: '/?c=Parodontogram&m=doSave',
            failure: function() {
                loadMask.hide();
            },
            success: function(response) {
                loadMask.hide();
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if (response_obj.success == false) {
                    //sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
                } else {
                    options.callback(response_obj);
                }
            }
        });
        return true;
    },
    initComponent: function()
    {
        var me = this;

        me.toothStateValuesMenuStore = new Ext.data.Store({
            autoLoad: false,
            reader: sw.Promed.StomHelper.ToothStateValues.getStoreReader()
        });

        me.historyStore = new Ext.data.Store({
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                id: 'EvnUslugaStom_id'
            }, [{
                mapping: 'EvnUslugaStom_id',
                name: 'EvnUslugaStom_id',
                type: 'string'
            }, {
                mapping: 'Lpu_id',
                name: 'Lpu_id',
                type: 'string'
            }, {
                mapping: 'MedPersonal_id',
                name: 'MedPersonal_id',
                type: 'string'
            }, {
                mapping: 'EvnUslugaStom_setDate',
                name: 'EvnUslugaStom_setDate',
                type: 'string'
            }, {
                mapping: 'EvnUslugaStom_setTime',
                name: 'EvnUslugaStom_setTime',
                type: 'string'
            }, {
                mapping: 'EvnUslugaStom_Display',
                name: 'EvnUslugaStom_Display',
                type: 'string'
            }]),
            url: '/?c=Parodontogram&m=doLoadHistory',
            listeners: {
                load: function(store, records, options) {
                    if (options && options.params && (me.isNewEvnUslugaStom() || me.isNewParodontogram())) {
                        //log({debug: 'load', args: arguments});
                        var recs = [], key;
                        recs.push({
                            EvnUslugaStom_id: 0,
                            EvnUslugaStom_Display: 'Новая',
                            EvnUslugaStom_setDate: '',
                            EvnUslugaStom_setTime: '',
                            MedPersonal_id: null,
                            Lpu_id: null
                        });
                        for (key in records) {
                            if (records[key].data) {
                                recs.push(records[key].data);
                            }
                        }
                        store.removeAll();
                        store.loadData(recs);
                    }
                }
            }
        });

        me.historyComboBox = new Ext.form.ComboBox({
            enableKeyEvents: false,
            editable: false,
            allowBlank: false,// чтобы убрать пустую запись
            //fieldLabel: '',
            emptyText: langs('новая'),
            hideLabel: true,
            forceSelection: true,
            width: 280,
            listWidth: 330,
            mode: 'remote',
            //onTriggerClick: function() { log(arguments); },
            //triggerClass: 'hideTrigger',
            //resizable: false,
            selectOnFocus: false,
            triggerAction: 'all',
            displayField: 'EvnUslugaStom_Display',
            hiddenName: 'EvnUslugaStom_hid',
            valueField: 'EvnUslugaStom_id',
            store: me.historyStore,
            doBack: function() {
                var oldValue = this.getValue(),
                    index = this.getStore().indexOfId(oldValue),
                    newRec = this.getStore().getAt(index + 1);
                if (newRec) {
                    this.setValue(newRec.get('EvnUslugaStom_id'));
                    this.fireEvent('change', this, this.getValue(), oldValue);
                }
            },
            doForward: function() {
                var oldValue = this.getValue(),
                    index = this.getStore().indexOfId(oldValue),
                    newRec = this.getStore().getAt(index - 1);
                if (newRec) {
                    this.setValue(newRec.get('EvnUslugaStom_id'));
                    this.fireEvent('change', this, this.getValue(), oldValue);
                }
            }
        });

        me.historyBackBtn = new Ext.Button({
            handler: function() {
                me.historyComboBox.doBack();
            },
            disabled: true,
            hidden: true,
            hideMode: 'visibility',
            tooltip : langs('К предыдущему состоянию'),
            iconCls: 'back16',
            text: '&nbsp;'
        });
        me.historyForwardBtn = new Ext.Button({
            handler: function() {
                me.historyComboBox.doForward();
            },
            disabled: true,
            hidden: true,
            hideMode: 'visibility',
            tooltip : langs('К следущему состоянию'),
            iconCls: 'forward16',
            style: 'margin-left: 12px',
            text: '&nbsp;'
        });

        me.historyComboBox.addListener({
            change: function(field, newValue, oldValue) {
                var index = field.getStore().indexOfId(newValue),
                    rec = field.getStore().getAt(index),
                    prevRec = field.getStore().getAt(index + 1),
                    nextRec = field.getStore().getAt(index - 1),
                    forwardDisabled = (!nextRec || !nextRec.get('EvnUslugaStom_id')),
                    backDisabled = (!prevRec || !prevRec.get('EvnUslugaStom_id'));
                if (rec && !me.isLoadHistoryByPerson) {
                    me.doLoadViewData(rec.get('EvnUslugaStom_id'));
                } else {
                    me.doLoadViewData(0);
                }
                me.historyBackBtn.setDisabled(backDisabled);
                me.historyBackBtn.setVisible(!backDisabled);
                me.historyForwardBtn.setDisabled(forwardDisabled);
                me.historyForwardBtn.setVisible(!forwardDisabled);
                field.clearInvalid();
            }
        });

        me.mainViewPanelStore = new Ext.data.Store({
            autoLoad: false,
            listeners: {
                'load': function() {
                    var onLoad = function(){
                        var isRefresh = me.mainViewPanel.refresh();
                        if (isRefresh && me.getPrintBtn()) {
                            me.getPrintBtn().setDisabled(false);
                        }
                    };
                    if (me.isEnableEdit()) {
                        if (me.toothStateValuesMenuStore.getCount() == 0) {
                            sw.Promed.StomHelper.ToothStateValues.loadStore(
                                me.toothStateValuesMenuStore,
                                onLoad
                            );
                        } else {
                            onLoad();
                        }
                    } else {
                        onLoad();
                    }
                }
            },
            reader: new Ext.data.JsonReader({
                id: 'UslugaComplex_Code'
            }, [{
                mapping: 'UslugaComplex_Code',
                name: 'UslugaComplex_Code'
            }, {
                mapping: 'UslugaComplex_Name',
                name: 'UslugaComplex_Name'
            }, {
                mapping: 'parodontogramma',
                name: 'parodontogramma'
            }, {
                mapping: 'state',
                name: 'state'
            }]),
            url: '/?c=Parodontogram&m=doLoadViewData'
        });

        me.mainViewPanel = new Ext.Panel({
            region: 'center',
            collapsible: false,
            layout: 'fit',
            border: false,
            autoHeight: true,
            bodyStyle: 'background-color: #fff; padding: 10px;',
            refresh : function()
            {
                var rec = me.mainViewPanelStore.getAt(0);
                if (!rec) {
                    this.body.update('<p>Извините, не удалось загрузить пародонтограмму...</p>');
                    return false;
                }
                this.updateParodontogramma(this.body);
                var tooth_list = Ext.query("td[class*=parodont]", this.body.dom);
                var i, el, clickEl;
                for (i=0; i < tooth_list.length; i++)
                {
                    el = new Ext.Element(tooth_list[i]);
                    if (el.hasClass('state-type-1')) {
                        el.setStyle('border', '1px solid #808080');
                    } else {
                        el.setStyle('border', '1px solid #808080');
                        el.setStyle('color', '#ff0000');
                        el.setStyle('background-color', '#ffcccc');
                    }
                    if (me.isEnableEdit()) {
                        el.on('click', me.showToothStateValuesMenu, me);
                        el.setStyle('cursor', 'pointer');
                    }
                }
                tooth_list = Ext.query("td[class*=tooth-state-value]", this.body.dom);
                for (i=0; i < tooth_list.length; i++)
                {
                    el = new Ext.Element(tooth_list[i]);
                    el.setStyle('color', 'gray');
                }
                tooth_list = Ext.query("td[class*=tooth-info]", this.body.dom);
                for (i=0; i < tooth_list.length; i++)
                {
                    el = new Ext.Element(tooth_list[i]);
                    el.setStyle('font-size', '11px');
                }
                return true;
            },
            /**
             * Обновляем данные
             */
            updateParodontogramma: function(body)
            {
                var rec = me.mainViewPanelStore.getAt(0);
                if (!rec) {
                    return false;
                }
                var tpl = new Ext.XTemplate(rec.get('parodontogramma')),
                    data = {
                        EvnUslugaStom_setDate: me.getParam('EvnUslugaStom_setDate'),
                        Sum1: 0,
                        Sum2: 0,
                        Sum3: 0,
                        Sum4: 0
                    },
                    toothCode,
                    jawCode,
                    state = rec.get('state');
                for (toothCode in state) {
                    data['TypeCode' + toothCode] = state[toothCode]['ToothStateType_Code'];
                    data['TypeNick' + toothCode] = state[toothCode]['ToothStateType_Nick'];
                    data['Value' + toothCode] = state[toothCode]['ToothState_Value'];
                    jawCode = state[toothCode]['JawPartType_Code'];
                    data['Sum' + jawCode] += state[toothCode]['ToothState_Value'];
                }
                data.Sum1 = data.Sum1.toFixed(2);
                data.Sum2 = data.Sum2.toFixed(2);
                data.Sum3 = data.Sum3.toFixed(2);
                data.Sum4 = data.Sum4.toFixed(2);
                tpl.overwrite(body, data);
                return true;
            },
            /**
             * Возвращаем строку для печати с клиента
             * @return {String}
             */
            getParodontogrammaHtml: function()
            {
                var body = new Ext.Element(document.createElement('body'));
                this.updateParodontogramma(body);
                return '<html><head><title>Печать</title>' +
                    '<style type="text/css">' +
                    'div.parodontogramma { margin: 20px; }' +
                    'table, span, div, td { font-family: tahoma,arial,helvetica,sans-serif;}' +
                    'td.state-type-1 { border: 1px solid #808080; }' +
                    'td.state-type-2 { border: 3px solid #000000; }' +
                    'td.state-type-3 { border: 3px solid #000000; }' +
                    'td.state-type-4 { border: 3px solid #000000; }' +
                    'td.state-type-5 { border: 3px solid #000000; }' +
                    'td.state-type-6 { border: 3px solid #000000; }' +
                    '</style>' +
                    '<style type="text/css" media="print">' +
                    '@page port { size: portrait }' +
                    '@page land { size: landscape }' +
                    '</style>' +
                    '</head>' +
                    '<body>'+ body.dom.innerHTML +'</body></html>';
            }
        });

        me.items = [
            me.mainViewPanel
        ];

        me.tbar = new sw.Promed.Toolbar({
            style: me.topToolbarStyle||'',
            buttons: [{
                handler: function() {
                    me.doPrint();
                },
                iconCls: 'print16',
                text: BTN_GRIDPRINT
            }, {
                handler: function() {
                    me.doDelete();
                },
				hidden: me.hiddenDelBtn,
                iconCls: 'delete16',
                text: BTN_GRIDDEL
            }, '->',
                me.historyBackBtn,
                ' ',
                me.historyComboBox,
                ' ',
                me.historyForwardBtn
            ]
        });

        sw.Promed.ParodontogramPanel.superclass.initComponent.apply(this, arguments);
        /*
        me.addListener({
            expand: function(panel) {
                panel.doLayout();
            }
        });*/
    }
});

/**
 * Панель отображения состава комплексной услуги и выбора позиций
 * @package      libs
 * @access       public
 * @autor		 Alexander Permyakov
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @version      05.2014
 */
sw.Promed.UslugaComplexCompositionPanel = Ext.extend(Ext.tree.TreePanel, {
    title: 'Состав комплексной услуги',//пакета услуг
    height: 180,
    autoWidth: true,
    autoScroll:true,
    animate:false,
    enableDD:false,
    containerScroll: true,
    rootVisible: false,
    autoLoad:false,
    //trackMouseOver: false,
    frame: true,
    root: {
        nodeType: 'async'
    },
    cls: 'x-tree-noicon',

    evnClassSysNick: null,
    _changing: false,
    _isReadOnly: true,
    _tariffParams: {},
    _params: {
        UslugaComplex_id: null,
        UslugaComplexMedService_id: null,
        UslugaComplexLevel_id: null,
        uslugaList: null // array or string
    },
    applyParams: function(uslugaList, UslugaComplex_id, UslugaComplexMedService_id, UslugaComplexLevel_id)
    {
        this._params.uslugaList = uslugaList || null;
        this._params.UslugaComplex_id = UslugaComplex_id || null;
        this._params.UslugaComplexMedService_id = UslugaComplexMedService_id || null;
        this._params.UslugaComplexLevel_id = UslugaComplexLevel_id || null;
    },
    doReset: function()
    {
        this._params.uslugaList = null;
        this._params.UslugaComplexMedService_id = null;
        this._params.UslugaComplex_id = null;
        this._params.UslugaComplexLevel_id = null;
        this._params.Mes_id = null;
        this._tariffParams = {};
        this.doClear();
        this.setReadOnly(true);
    },
    setParam: function(key, value)
    {
        this._params[key] = value;
    },
    getParam: function(key)
    {
        return this._params[key] || '';
    },
    hasChanges: function()
    {
        return (this.getCheckedUslugaList(true) != this.getParam('uslugaList').toString());
    },
    hasComposition: function()
    {
        return (this.getRootNode().childNodes.length > 0);
    },
    isUslugaComplex: function()
    {
        return (this.getParam('UslugaComplexLevel_id') == 2);
    },
    isUslugaComplexPackage: function()
    {
        return (this.getParam('UslugaComplexLevel_id') == 9);
    },
    getCheckedUslugaList: function(asString)
    {
        var nodes = this.getChecked(),
            checked = [];
        for (var i=0; i < nodes.length; i++)
        {
            if (nodes[i].childNodes.length == 0) {
                checked.push(nodes[i].attributes.id);
            }
        }
        if (asString) {
            return checked.toString();
        }
        return checked;
    },
    getUslugaComplexSelectedList: function(asString)
    {
        var id, record, arr = [], Row = function(uc, id, ued, uem, tariff) {
            this.UslugaComplex_id = uc;
            this.UslugaComplexTariff_id = id || null;
            this.UslugaComplexTariff_UED = Number(ued);
            this.UslugaComplexTariff_UEM = Number(uem);
            this.UslugaComplexTariff_Tariff = Number(tariff);
        };
        for (id in this.selectedUslugaComplexTariff) {
            record = this.selectedUslugaComplexTariff[id];
            if (record.data && record.get('UslugaComplexTariff_id')) {
                arr.push(new Row(id, record.get('UslugaComplexTariff_id'), 
                    record.get('UslugaComplexTariff_UED'), 
                    record.get('UslugaComplexTariff_UEM'), 
                    record.get('UslugaComplexTariff_Tariff')
                ));
            } else {
                //if (this.evnClassSysNick == 'EvnUslugaStom')
                arr.push(new Row(id, null, Number(record), null, null));
            }
        }
        if (asString && 0 == arr.length) {
            return '';
        }
        if (asString) {
            return Ext.util.JSON.encode(arr);
        }
        return arr;
    },
    isReadOnly: function()
    {
        return this._isReadOnly;
    },
    setReadOnly: function(isReadOnly)
    {
        this._isReadOnly = isReadOnly;
    },
    isChooseUslugaComplexTariff: function(node)
    {
        var tariffParams = this.getTariffParams(),
            isPackComposition = (node && node.attributes.UslugaComplex_pid
                && this.getUslugaComplexParentValue() == node.attributes.UslugaComplex_pid);
        return ((isPackComposition || this.getRootNode().id == node.id) 
            && tariffParams && tariffParams.LpuSection_id && tariffParams.PayType_id
            && tariffParams.Person_id && tariffParams.UslugaComplexTariff_Date);
    },
    getUslugaComplexParentAttr: function()
    {
        var attr = null;
        if (this.getParam('Mes_id')>0 && this.getParam('EvnUsluga_pid')>0) {
            attr = 'Mes_id';
        }
        if (this.getParam('UslugaComplex_id')>0) {
            attr = 'UslugaComplex_id';
        }
        if (this.getParam('UslugaComplexMedService_id')>0) {
            attr = 'UslugaComplexMedService_id';
        }
        return attr;
    },
    getUslugaComplexParentValue: function()
    {
        var attr = this.getUslugaComplexParentAttr();
        if (!attr) {
            return null;
        }
        return this.getParam(attr);
    },
    getTariffParams: function()
    {
        return this._tariffParams;
    },
    setTariffParams: function(params)
    {
        if (Ext.isDate(params.UslugaComplexTariff_Date)) {
            params.UslugaComplexTariff_Date = Ext.util.Format.date(params.UslugaComplexTariff_Date, 'd.m.Y');
        }
        this._tariffParams = params;
    },
    isDefaultChecked: function(node)
    {
        var recursiveIsByMes = function(node) {
            if (!node) {
                return false;
            }
            if (!node.attributes.UslugaComplex_isMes) {
                return recursiveIsByMes(node.parentNode);
            }
            return (node.attributes.UslugaComplex_isMes == 2);
        };
        // Все услуги состава должны быть отмечены по умолчанию (если нет условий по МЭС)
        if (this.getParam('Mes_id') && !this.getParam('EvnUsluga_pid')) {
            return recursiveIsByMes(node);
        } else {
            return true;
        }
    },
    doClear: function()
    {
        var root_node = this.getRootNode();
        while (root_node.childNodes.length > 0) {
            root_node.removeChild( root_node.childNodes[0] );
        }
        this.selectedUslugaComplexTariff = {};
    },
    doLoad: function(params)
    {
        var me = this,
            callback = Ext.emptyFn;
        if (params && params.UslugaComplex_id) {
            me.setParam('UslugaComplex_id', params.UslugaComplex_id);
        }
        if (params && params.UslugaComplexMedService_id) {
            me.setParam('UslugaComplexMedService_id', params.UslugaComplexMedService_id);
        }
        if (params && params.callback) {
            callback = params.callback;
        }
        me.doClear();
        me.getLoader().load(me.getRootNode(), callback);
    },
    recountUslugaComplexTariff: function(node, record)
    {
        if (!record || (record.data && !record.get('UslugaComplexTariff_id'))) {
            record = null;
        }
        if (node.attributes.checked && record) {
            this.selectedUslugaComplexTariff[node.id] = record;
        } else if (this.selectedUslugaComplexTariff[node.id]) {
            delete this.selectedUslugaComplexTariff[node.id];
        }
        var id, cnt = 0, ued = 0, uem = 0, price = 0;
        for (id in this.selectedUslugaComplexTariff) {
            cnt++;
            record = this.selectedUslugaComplexTariff[id];
            if (record.data) {
                ued += Number(record.get('UslugaComplexTariff_UED'));
                uem += Number(record.get('UslugaComplexTariff_UEM'));
                price += Number(record.get('UslugaComplexTariff_Tariff'));
            } else {
                ued += Number(record);
            }
        }
        this.onRecountUslugaComplexTariff(cnt, ued, uem, price);
    },
    onRecountUslugaComplexTariff: function(cnt, ued, uem, price)
    {
        // реализовать в форме
    },
    _createLoader: function()
    {
        var me = this;
        me.loader = new Ext.tree.TreeLoader({
            dataUrl:'/?c=MedService&m=loadCompositionTree',
            uiProviders: {'default': Ext.tree.TreeNodeUI, tristate: Ext.tree.TreeNodeTriStateUI},
            listeners:
            {
                load: function(tl, node)
                {
                    me._onLoad(tl, node);
                },
                beforeload: function (tl, node)
                {
                    me._onBeforeLoad(tl, node);
                }
            }
        });
    },
    _onLoad: function(tl, node)
    {
        var me = this;
        var nodes = node.childNodes || [];
        for (var i=0; i < nodes.length; i++) {
            nodes[i].getUI().toggleCheck(me.isDefaultChecked(nodes[i]));
            me._renderUslugaComplexTariffCombo(nodes[i]);
        }
    },
    _onBeforeLoad: function(tl, node)
    {
        var me = this;
        var param_usluga = me.getUslugaComplexParentAttr();
        tl.baseParams = {};
        // чтобы показать чекбокс
        tl.baseParams.check = 1;
        if (me.getParam('Mes_id')) {
            // чтобы знать входит ли услуга из состава в указанный МЭС
            tl.baseParams.Mes_id = me.getParam('Mes_id');
        }
        if (node.getDepth()==0) {
            if (me.isChooseUslugaComplexTariff(node)) {
                // требуется выбор тарифов услуги из состава
                tl.baseParams.chooseUslugaComplexTariff = 1;
            }
            if (param_usluga) {
                tl.baseParams[param_usluga] = me.getParam(param_usluga);
            } else {
                // Если попытка загрузить данные выполняется при рендеринге
                return false;
            }
        } else {
            tl.baseParams[node.attributes.object_id] = node.attributes.object_value;
        }
        return true;
    },
    _renderUslugaComplexTariffCombo: function(node)
    {
        var me = this;
        if (me.isChooseUslugaComplexTariff(node)) {
            // требуется выбор тарифов услуг
            node.on('beforeclick', function(n, e){
                var el = new Ext.Element(e.target);
                return ( el && (-1 == el.id.indexOf('_UslugaComplexTariff_')) );
            });
            var elId = 'chooseUslugaComplexTariffWrap' + node.id,
                tariffParams = me.getTariffParams(),
                cmp;
            tariffParams['UslugaComplex_id'] = node.id;
            //log({debug: 'renderUslugaComplexTariffCombo', node: node, el: Ext.get(elId)});
            if (Ext.get(elId)) {
                cmp = new sw.Promed.SwUslugaComplexTariffCombo({
                    id: me.getId() + '_UslugaComplexTariff_' + node.id,
                    UslugaComplexNode: node,
                    renderTo: elId,
                    hiddenName: 'UslugaComplexTariff_' + node.id,
                    isStom: true,
                    allowBlank: false,
                    listWidth: 500,
                    width: 250,
                    listeners: {
                        change: function (combo, newValue, oldValue) {
                            var index = combo.getStore().findBy(function(rec) {
                                return (rec.get(combo.valueField) == newValue);
                            });
                            combo.fireEvent('select', combo, combo.getStore().getAt(index));
                            return true;
                        },
                        select: function (combo, record) {
                            me.recountUslugaComplexTariff(combo.UslugaComplexNode, record);
                        }
                    }
                });
                cmp.setParams(tariffParams);
                cmp.getStore().elId = elId;
                cmp.getStore().ownerCmp = cmp;
                cmp.getStore().UslugaComplexNode = node;
                cmp.getStore().on('load', function(store){
                    if (store.getCount() == 0) {
                        function func() {
                            store.ownerCmp.destroy();
                            var f = new Ext.form.NumberField({
                                id: me.getId() + '_UslugaComplexTariff_' + store.UslugaComplexNode.id,
                                UslugaComplexNode: store.UslugaComplexNode,
                                renderTo: store.elId,
                                width: 250,
                                name: 'UslugaComplexTariff_UED_' + store.UslugaComplexNode.id,
                                maxValue: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ? 15 : 20),
                                allowDecimals: true,
                                allowNegative: false,
                                listeners: {
                                    change: function (field, newValue, oldValue) {
                                        me.recountUslugaComplexTariff(field.UslugaComplexNode, newValue);
                                    }
                                }
                            });
                        }
                        setTimeout(func, 1000);
                    }
                });
                cmp.loadUslugaComplexTariffList();
            }
        }
    },
    _onInitComponent: function()
    {
        var me = this;
        me.on('click', function(node)
        {
            /*log({
             debug: 'click',
             node: node,
             checked: node.attributes.checked
             });*/
            node.getUI().toggleCheck(!node.attributes.checked);
        });
        me.on('checkchange', function (node, checked)
        {
            if (!me._changing) {
                me._changing = true;
                var uctc = Ext.getCmp(me.getId() + '_UslugaComplexTariff_' + node.id),
                    uctcRecord;
                if (uctc) {
                    if (typeof uctc.getStore == 'function') {
                        uctcRecord = uctc.getStore().getById(uctc.getValue());
                    } else {
                        uctcRecord = uctc.getValue();
                    }
                    me.recountUslugaComplexTariff(node, uctcRecord);
                }
                node.expand(true, false);
                if (checked)
                    node.cascade( function(node){node.getUI().toggleCheck(true)} );
                else
                    node.cascade( function(node){node.getUI().toggleCheck(false)} );
                node.bubble( function(node){if (node.parentNode) node.getUI().updateCheck()} );
                me._changing = false;
            }
        });
    },
    _onBeforeInitComponent: function()
    {
        var me = this;
        me._createLoader();
    },
    initComponent: function()
    {
        var me = this;
        me._onBeforeInitComponent();
        sw.Promed.UslugaComplexCompositionPanel.superclass.initComponent.apply(me, arguments);
        me._onInitComponent();
    }
});

/**
 * Панель отображения услуг из состава пакета и выбора позиций
 * @package      libs
 * @access       public
 * @autor		 Alexander Permyakov
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @version      07.2014
 */
sw.Promed.UslugaSelectPanel = Ext.extend(sw.Promed.ViewFrame, {
    title: langs('Состав пакета услуг'),
    height: 200,
    noSelectFirstRowOnFocus: true,
    useEmptyRecord: false,
    showCountInTop: false,
    checkBoxWidth: 25,
    border: true,
    selectionModel: 'multiselect',
    multi: true,
    singleSelect: false,
    stateful: true,
    layout: 'fit',
    groups:false,
    autoLoadData: false,
    object: 'UslugaComplex',
    dataUrl: '/?c=UslugaComplex&m=loadForSelect',
    autoExpandColumn: 'autoexpand',
    saveAtOnce: false,
    saveAllParams: false,
    clicksToEdit: 1,
    stringfields:[
        {name: 'UslugaComplex_id', type: 'string', header: 'ID', key: true},
        {name: 'UslugaComplex_IsByMes', type: 'int', hidden: true},
        {
            name: 'UslugaComplex_Code',
            header: langs('Код'), headerAlign: 'right',
            align: 'right', width: 70,  type: 'string'
        },
        {
            name: 'UslugaComplex_Name', id: 'autoexpand',
            header: langs('Наименование'), headerAlign: 'center',
            align: 'left', width: 250, type: 'string'
        },
        {name: 'UslugaComplexTariff_Count', type: 'int', hidden: true},
        {name: 'UslugaComplexTariff_id', type: 'int', hidden: true},
        {name: 'UslugaComplexTariff_Tariff', type: 'float', hidden: true},
        {name: 'UslugaComplexTariff_UED', type: 'float', hidden: true},
        {name: 'UslugaComplexTariff_UEM', type: 'float', hidden: true},
        {
            name: 'EvnUsluga_Kolvo',
            header: langs('Количество'),
            headerAlign: 'center',
            align: 'center', 
            editor: new Ext.form.NumberField({
                width: 80,
                allowBlank: false,
                allowDecimals: false,
                allowNegative: false,
                enableKeyEvents: true,
                listeners: {
                    keydown: function (inp, e) {
                        if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
                            e.stopEvent();
                            //todo установить фокус в поле УЕТ
                            //log({debug: 'onTabKolvo', args: arguments});
                        }
                    }
                }
            }),
            width: 90
        },
        {
            name: 'UslugaComplexTariffUed',
            header: langs('УЕТ'),
            headerAlign: 'center',
            align: 'center', 
            renderer: function(value, cellEl, rec, row, origId, store) {
                var ued = parseFloat(rec.get('UslugaComplexTariff_UED')) || 0;
                value = '<div id="UslugaComplexTariffUedOutput_' 
                    + rec.get(store.idProperty) 
                    + '" onclick="Ext.getCmp(\'' 
                    + store.id.replace('GridStore', '')
                    + '\').onClickUedCell('
                    + "'"+ rec.get(store.idProperty) +"'"
                    + ')">' 
                    + ued 
                    + '</div>'
                    + '<div id="UslugaComplexTariffUedInput_' 
                    + rec.get(store.idProperty) 
                    + '" style="display: none"></div>';
                return value;
            },
            width: 200
        }
    ],
    toolbar: false,
    actions:[
        {name:'action_add', hidden: true, disabled: true},
        {name:'action_edit', hidden: true, disabled: true},
        {name:'action_view', hidden: true, disabled: true},
        {name:'action_delete', hidden: true, disabled: true},
        {name:'action_refresh', hidden: true, disabled: true},
        {name:'action_save', hidden: true, disabled: true},
        {name:'action_print', hidden: true, disabled: true}
    ],
	isAllowSelectionChange: function(g, rowIndex, e) {
		var el = new Ext.Element(e.getTarget()),
			cellEl = el && el.findParent('td[class*=x-grid3-cell]', 7, true);
		if (cellEl) {
			return (!cellEl.hasClass('x-grid3-td-10') && !cellEl.hasClass('x-grid3-td-11'));
		}
		return true;
	},
    _evnClassSysNick: null,
    _tariffParams: {},
    isValidTariffParams: function(node)
    {
        var tariffParams = this.getTariffParams();
        return (tariffParams && tariffParams.LpuSection_id && tariffParams.PayType_id
            && tariffParams.Person_id && tariffParams.UslugaComplexTariff_Date);
    },
    getTariffParams: function()
    {
        return this._tariffParams;
    },
    setTariffParams: function(params)
    {
        if (Ext.isDate(params.UslugaComplexTariff_Date)) {
            params.UslugaComplexTariff_Date = Ext.util.Format.date(params.UslugaComplexTariff_Date, 'd.m.Y');
        }
        this._tariffParams = params;
    },
    _params: {
        UslugaComplex_id: null,
        UslugaComplexLevel_id: null,
        EvnUsluga_pid: null,
        Mes_id: null
    },
    applyParams: function(EvnUsluga_pid, UslugaComplex_id, Mes_id, UslugaComplexLevel_id)
    {
        this._params.EvnUsluga_pid = EvnUsluga_pid || null;
        this._params.UslugaComplex_id = UslugaComplex_id || null;
        this._params.Mes_id = Mes_id || null;
        this._params.UslugaComplexLevel_id = UslugaComplexLevel_id || null;
    },
    doReset: function()
    {
        this._params.EvnUsluga_pid = null;
        this._params.Mes_id = null;
        this._params.UslugaComplex_id = null;
        this._params.UslugaComplexLevel_id = null;
        this._tariffParams = {};
        this.doClear();
        //this.setReadOnly(true);
    },
    setParam: function(key, value)
    {
        this._params[key] = value;
    },
    getParam: function(key)
    {
        return this._params[key] || '';
    },
    isUslugaComplex: function()
    {
        return (this.getParam('UslugaComplexLevel_id') == 2);
    },
    isUslugaComplexPackage: function()
    {
        return (this.getParam('UslugaComplexLevel_id') == 9);
    },
    validateUslugaSelectedList: function()
    {
        var me = this, 
            errMsg = '',
            selected = me.getUslugaSelectedList(false),
            i;
        if (selected.length == 0) {
            return langs('Вы не выбрали ни одной услуги');
        }
        for (i = 0; i < selected.length; i++) {
            if (!me.isAllowEmptyUed() && !me.isDisableUed() && !selected[i].UslugaComplexTariff_UED) {
                errMsg = langs('Вы не ввели УЕТ врача у одной или нескольких выбранных услуг');
                break;
            }
        }
        return errMsg;
    },
    getUslugaSelectedList: function(asString)
    {
        var me = this, 
            id, record, 
            arr = [], 
            Row = function(uc, id, ued, uem, tariff, kolvo) {
                this.UslugaComplex_id = uc;
                this.UslugaComplexTariff_id = id || null;
                this.UslugaComplexTariff_UED = Number(ued);
                this.UslugaComplexTariff_UEM = Number(uem);
                this.UslugaComplexTariff_Tariff = Number(tariff);
                if (!kolvo || kolvo < 1) {
                    kolvo = 1;
                }
                this.EvnUsluga_Kolvo = parseInt(kolvo);
                var price = null,
                    summa = null;
                if ( this.EvnUsluga_Kolvo > 0 ) {
                    price = 0;
                    if ( !me.isDisableUed() && this.UslugaComplexTariff_UED > 0 ) {
                        price += this.UslugaComplexTariff_UED;
                    }
                    if ( !me.isDisableUem() && this.UslugaComplexTariff_UEM > 0 ) {
                        price += this.UslugaComplexTariff_UEM;
                    }
                    summa = price * this.EvnUsluga_Kolvo;
                }
                this.getPrice = function () { return price; };
                this.getSumma = function () { return summa; };
            };
        for (id in me.selectedUslugaComplexTariff) {
            record = me.selectedUslugaComplexTariff[id];
            arr.push(new Row(id, record.get('UslugaComplexTariff_id'), 
                record.get('UslugaComplexTariff_UED'), 
                record.get('UslugaComplexTariff_UEM'), 
                record.get('UslugaComplexTariff_Tariff'), 
                record.get('EvnUsluga_Kolvo')
            ));
        }
        if (asString && 0 == arr.length) {
            return '';
        }
        if (asString) {
            return Ext.util.JSON.encode(arr);
        }
        return arr;
    },
    getCheckedUslugaList: function(asString)
    {
        var selections = this.getGrid().getSelectionModel().getSelections(),
            checked = [];
        for (var key in selections) {
            if (selections[key].data) {
                checked.push(selections[key].data[this.jsonData['key_id']]);
            }
        }
        if (asString) {
            return checked.toString();
        }
        return checked;
    },
    onClickUedCell: function(id) {
        var me = this,
            outputEl = Ext.get('UslugaComplexTariffUedOutput_' + id),
            inputEl = Ext.get('UslugaComplexTariffUedInput_' + id),
            rec = me.getGrid().getStore().getById(id),
            parent = outputEl ? new Ext.Element(outputEl.dom.parentNode) : null,
            parentStyle = parent ? parent.getAttribute('style') : null,
            value = null,
            cmp,
            onBlurCmp = function(cmp, value) {
                outputEl.update(value);
                outputEl.setDisplayed(true);
                inputEl.setDisplayed(false);
                cmp.destroy();
                parent.setAttribute('style', parentStyle);
            };
        if (!rec || !outputEl || !outputEl || !parent) {
            return false;
        }
        parent.setAttribute('style', 'padding: 0; margin: 0;');
        outputEl.setDisplayed(false);
        inputEl.setDisplayed(true);
        if (rec.get('UslugaComplexTariff_Count') > 0) {
            if (rec.get('UslugaComplexTariff_Count') == 1) {
                value = rec.get('UslugaComplexTariff_id');
            }
            cmp = new sw.Promed.SwUslugaComplexTariffCombo({
                id: me.getId() + '_UslugaComplexTariff_' + rec.id,
                renderTo: inputEl.id,
                hiddenName: 'UslugaComplexTariff_' + rec.id,
                value: value,
                isStom: ('EvnUslugaStom' == me.evnClassSysNick),
                allowBlank: false,
                listWidth: 500,
                width: 200,
                listeners: {
                    blur: function (combo) {
                        var tariff = combo.getStore().getById(combo.getValue());
                        if (tariff) {
                            rec.set('UslugaComplexTariff_id', tariff.get('UslugaComplexTariff_id'));
                            rec.set('UslugaComplexTariff_UED', tariff.get('UslugaComplexTariff_UED'));
                            rec.set('UslugaComplexTariff_UEM', tariff.get('UslugaComplexTariff_UEM'));
                            rec.set('UslugaComplexTariff_Tariff', tariff.get('UslugaComplexTariff_Tariff'));
                            rec.commit();
                            me.recountUslugaComplexTariff(rec);
                            onBlurCmp(combo, rec.get('UslugaComplexTariff_UED'));
                        }
                    },
                    keydown: function (inp, e) {
                        if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
                            e.stopEvent();
                            //todo установить фокус в поле кол-во на следующей строке
                            //log({debug: 'onTabUED', args: arguments});
                        }
                    }
                }
            });
            var params = me.getTariffParams();
            params.UslugaComplex_id = rec.get('UslugaComplex_id');
            cmp.setParams(params);
            cmp.loadUslugaComplexTariffList();
        } else {
            value = rec.get('UslugaComplexTariff_UED') || 0;
            cmp = new Ext.form.NumberField({
                id: me.getId() + '_UslugaComplexTariff_' + rec.id,
                renderTo: inputEl.id,
                name: 'UslugaComplexTariff_' + rec.id,
                value: value,
                width: 200,
                allowBlank: false,
                allowDecimals: true,
                allowNegative: false,
                enableKeyEvents: true,
                listeners: {
                    blur: function (field) {
                        var v = field.getValue() || 0;
                        rec.set('UslugaComplexTariff_UED', v);
                        rec.commit();
                        me.recountUslugaComplexTariff(rec);
                        onBlurCmp(field, v);
                    },
                    keydown: function (inp, e) {
                        if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
                            e.stopEvent();
                            //todo установить фокус в поле кол-во на следующей строке
                            //log({debug: 'onTabUED', args: arguments});
                        }
                    }
                }
            });
        }
        cmp.focus(true, 250);
        return true;
    },
    onAfterEdit: function(o) {
        //log({debug: 'onAfterEdit', obj: o});
        o.grid.stopEditing(true);
        o.record.commit();
        this.recountUslugaComplexTariff(o.record);
    },
    onRowSelect: function(sm,rowIdx,record)
    {
        this.recountUslugaComplexTariff(record);
    },
    onRowDeSelect: function(sm,rowIdx,record)
    {
        this.recountUslugaComplexTariff(record);
    },
    onLoadData: function()
    {
        var me = this,
            grid = me.getGrid(),
            store = grid.getStore(),
            sm = grid.getSelectionModel(),
            records = [];
        //log({debug: 'onLoadData', sm: sm, me: me, cnt: store.getCount()});
        if (store.getCount() > 0) {
            store.each(function(rec){
                var index = store.indexOf(rec);
                if (me.isDefaultChecked(rec)) {
                    records.push(rec);
                    //sm.selectRow(index);//selectRecords
                    sm.fireEvent('rowselect', sm, index, rec);
                }
            });
            sm.selectRecords(records);
        }
    },
	defaultCheck: true,
    isDefaultChecked: function(rec)
    {
        // Все услуги должны быть отмечены по умолчанию (если нет условий по МЭС)
		if ( this.defaultCheck === false ) {
			return false;
		}
        else if (this.getParam('Mes_id') && !this.getParam('EvnUsluga_pid')) {
            return (rec.get('UslugaComplex_IsByMes') == 2);
        } else {
            return true;
        }
    },
    recountUslugaComplexTariff: function(record)
    {
        var me = this,
            id = record.get('UslugaComplex_id') + '',
            isChecked = id.inlist(me.getCheckedUslugaList()),
            selected = [],
            tariff = 0, ued = 0, uem = 0, price = 0, sum = 0,
            sumField = me.getEvnUslugaSummaField(),
            tariffField = me.getEvnUslugaTariffField(),
            uemField = me.getEvnUslugaUEMField(),
            uedField = me.getEvnUslugaUEDField();
        if (isChecked) {
            me.selectedUslugaComplexTariff[id] = record;
        } else if (me.selectedUslugaComplexTariff[id]) {
            delete me.selectedUslugaComplexTariff[id];
        }
        selected = me.getUslugaSelectedList(false);
        if (selected.length == 0) {
            if (sumField) sumField.setValue('');
            if (!me.isDisableUem() && uemField) uemField.setValue('');
            if (!me.isDisableUed() && uedField) uedField.setValue('');
            if (!me.isDisableTariff() && tariffField) tariffField.setValue('');
        }
        for (id = 0; id < selected.length; id++) {
            ued += selected[id].UslugaComplexTariff_UED;
            uem += selected[id].UslugaComplexTariff_UEM;
            tariff += selected[id].UslugaComplexTariff_Tariff;
            price += selected[id].getPrice();
            sum += selected[id].getSumma();
        }
        if (tariffField) {
            if ( !me.isDisableTariff() ) {
                tariffField.setValue(tariff || '');
            } else {
                tariffField.setValue('');
            }
        }
        if (uedField) {
            if ( !me.isDisableUed() ) {
                uedField.setValue(ued || '');
            } else {
                uedField.setValue('');
            }
        }
        if (uemField) {
            if ( !me.isDisableUem() ) {
                uemField.setValue(uem || '');
            } else {
                uemField.setValue('');
            }
        }
        if (sumField) {
            sumField.setValue(sum || '');
        }
    },
    isDisableUem: function()
    {
        // реализовать в форме
        return false;
    },
	isAllowEmptyUed: function() {
		// реализовать в форме
		return false;
	},
    isDisableUed: function()
    {
        // реализовать в форме
        return false;
    },
    isDisableTariff: function()
    {
        return true;
    },
    getEvnUslugaSummaField: function()
    {
        // реализовать в форме
        return null;
    },
    getEvnUslugaTariffField: function()
    {
        // реализовать в форме
        return null;
    },
    getEvnUslugaUEDField: function()
    {
        // реализовать в форме
        return null;
    },
    getEvnUslugaUEMField: function()
    {
        // реализовать в форме
        return null;
    },
    getBaseForm: function()
    {
        // реализовать в форме
        return null;
    },
    doClear: function()
    {
        this.removeAll({clearAll:true});
        this.selectedUslugaComplexTariff = {};
    },
    doLoad: function(params)
    {
        var me = this,
            tariffParams = me.getTariffParams(),
            callback = Ext.emptyFn;
        if (params && params.UslugaComplex_id) {
            me.setParam('UslugaComplex_id', params.UslugaComplex_id);
        }
        if (params && params.EvnUsluga_pid) {
            me.setParam('EvnUsluga_pid', params.EvnUsluga_pid);
        }
        if (params && params.Mes_id) {
            me.setParam('Mes_id', params.Mes_id);
        }
        if (params && params.callback) {
            callback = params.callback;
        }
        me.doClear();
        tariffParams.UslugaComplex_id = me.getParam('UslugaComplex_id');
        tariffParams.EvnUsluga_pid = me.getParam('EvnUsluga_pid');
        tariffParams.Mes_id = me.getParam('Mes_id');
		if (!Ext.isEmpty(me.getParam('EvnDiagPLStom_id'))) {
			tariffParams.EvnDiagPLStom_id = me.getParam('EvnDiagPLStom_id');
		}
        me.loadData({
            globalFilters: tariffParams,
            callback: callback
        });
    }
});

/**
 * Панель отображения услуг по МЭС и выбора позиций
 * @package      libs
 * @access       public
 * @autor		 Alexander Permyakov
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @version      07.2014
 */
sw.Promed.UslugaSelectPanelByMes = Ext.extend(sw.Promed.UslugaSelectPanel, {
    title: langs('Услуги по МЭС'),
    _params: {
		EvnDiagPLStom_id: null,
        EvnUsluga_rid: null,
        EvnUsluga_pid: null,
        Mes_id: null
    },
    applyParams: function(EvnUsluga_pid, Mes_id, EvnUsluga_rid, EvnDiagPLStom_id)
    {
        this._params.EvnUsluga_pid = EvnUsluga_pid;
        this._params.Mes_id = Mes_id;
        this._params.EvnUsluga_rid = EvnUsluga_rid || null;
        this._params.EvnDiagPLStom_id = EvnDiagPLStom_id || null;
    },
    doReset: function()
    {
        this._params.EvnDiagPLStom_id = null;
        this._params.EvnUsluga_rid = null;
        this._params.EvnUsluga_pid = null;
        this._params.Mes_id = null;
        this._tariffParams = {};
        this.doClear();
    },
	stringfields: [
		{name: 'UslugaComplex_id', type: 'string', header: 'ID', key: true},
		{name: 'UslugaComplex_IsByMes', type: 'int', hidden: true},
		{
			name: 'UslugaComplex_Code',
			header: langs('Код'), headerAlign: 'right',
			align: 'right', width: 70,  type: 'string'
		},
		{
			name: 'UslugaComplex_Name', id: 'autoexpand',
			header: langs('Наименование'), headerAlign: 'center',
			align: 'left', width: 250, type: 'string'
		},
		{name: 'UslugaComplexTariff_Count', type: 'int', hidden: true},
		{name: 'UslugaComplexTariff_id', type: 'int', hidden: true},
		{name: 'UslugaComplexTariff_Tariff', type: 'float', hidden: true},
		{name: 'UslugaComplexTariff_UED', type: 'float', hidden: true},
		{name: 'UslugaComplexTariff_UEM', type: 'float', hidden: true},
		{
			name: 'EvnUsluga_Kolvo',
			header: langs('Количество'),
			headerAlign: 'center',
			align: 'center', 
			editor: new Ext.form.NumberField({
				width: 80,
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				enableKeyEvents: true,
				listeners: {
					keydown: function (inp, e) {
						if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
							e.stopEvent();
							//todo установить фокус в поле УЕТ
							//log({debug: 'onTabKolvo', args: arguments});
						}
					}
				}
			}),
			width: 90
		},
		{
			name: 'UslugaComplexTariffUed',
			header: langs('УЕТ'),
			headerAlign: 'center',
			align: 'center', 
			renderer: function(value, cellEl, rec, row, origId, store) {
				var ued = parseFloat(rec.get('UslugaComplexTariff_UED')) || 0;
				value = '<div id="UslugaComplexTariffUedOutput_' 
					+ rec.get(store.idProperty) 
					+ '" onclick="Ext.getCmp(\'' 
					+ store.id.replace('GridStore', '')
					+ '\').onClickUedCell('
					+ "'"+ rec.get(store.idProperty) +"'"
					+ ')">' 
					+ ued 
					+ '</div>'
					+ '<div id="UslugaComplexTariffUedInput_' 
					+ rec.get(store.idProperty) 
					+ '" style="display: none"></div>';
				return value;
			},
			width: 200
		}, {
			name: 'MesUsluga_IsNeedUsluga',
			header: langs('Обязательная'), headerAlign: 'left',
			align: 'center', width: 70, type: 'string'
		}
	]
});


sw.Promed.Tooth = function(el) {
    var me = this,
        extEl = el,
    //{Person_id}_{history_date}_{Tooth_Code}_{position}_{Tooth_SysNum}
        parts = extEl.id.split('_'),
        code =  parts[2],
        position = parts[3],
        sysNum = parts[4],
        surfacesPositions = [],
        surfaces = {},
        type = null,
        states = null,
        isAllowEditSurface = true,
        i, state,
        four = {
            xo0: 4,
            yo0: 24,
            xoN: 37,
            yoM: 48,
            xiL: 16,
            xiR: 25,
            yi: 36
        },
        five = {
            xo0: 4,
            yo0: 24,
            xoN: 37,
            yoM: 54,
            xi0: 13,
            yi0: 33,
            xiN: 28,
            yiM: 45
        },
        defineToothSurfaceTypeId = function(pos, isHasFiveSegments, isTop, isLeft)
        {
            var id = null;
            /*
             1	Вестибулярная (губная, щечная)
             3	Язычная
             2	Мезиальная
             4	Дистальная
             5	Окклюзионная (жевательная поверхность)
             */
            switch (true) {
                case (pos == 1): id = isTop ? 1 : 3; break;
                case (pos == 3): id = isTop ? 3 : 1; break;
                case (pos == 2): id = isLeft ? 2 : 4; break;
                case (pos == 4): id = isLeft ? 4 : 2; break;
                case (pos == 5 && isHasFiveSegments): id = 5; break;
            }
            return id;
        },
        isUpPoint = function(x0, y0, xp, yp, isSup)
        {
            var ys;
            if (isSup) {
                ys = y0 + (x0 - xp);
            } else {
                ys = y0 + (xp - x0);
            }
            return (ys > yp);
        };

    var jawPartCode = code.toString().charAt(0);
    if (jawPartCode > 4) {
        jawPartCode = jawPartCode - 4;
    }
    me.getJawPartCode = function() {
        return jawPartCode;
    };
    me.isLeft = function() {
        return (1 == jawPartCode || 4 == jawPartCode);
    };
    me.isTop = function() {
        return (1 == jawPartCode || 2 == jawPartCode);
    };

    if (me.isTop()) {
        four.yo0 += 12;
        four.yoM += 12;
        four.yi += 12;
        five.yo0 += 12;
        five.yoM += 12;
        five.yi0 += 12;
        five.yiM += 12;
    } else  {
        four.yo0 -= 12;
        four.yoM -= 12;
        four.yi -= 12;
        five.yo0 -= 12;
        five.yoM -= 12;
        five.yi0 -= 12;
        five.yiM -= 12;
    }

    /**
     * Определяем позицию поверхности зуба,
     * над которой находится курсор мыши
     */
    me.defineToothSurfacePos = function(e) {
        if (!e.getXY || !extEl.getXY || !me.isAllowEditSurface()) {
            return 0;
        }
        var x = parseInt(e.getXY()[0] - extEl.getXY()[0]),
            y = parseInt(e.getXY()[1] - extEl.getXY()[1]),
            s = 0,
            isXi1 = false,
            isXi2 = false,
            isXi3 = false,
            isYi1 = false,
            isYi2 = false,
            isYi3 = false;
        //log([x,y]);
        if (me.isHasFiveSegments()) {
            isXi1 = (x >= five.xo0 && x < five.xi0);// 4-12 1|4|3
            isXi2 = (x >= five.xi0 && x <= five.xiN);// 13-28 1|5|3
            isXi3 = (x > five.xiN && x <= five.xoN);// 29-37 1|2|3
            isYi1 = (y >= five.yo0 && y < five.yi0);// 24-32 4|1|2
            isYi2 = (y >= five.yi0 && y <= five.yiM);// 33-45 4|5|2
            isYi3 = (y > five.yiM && y <= five.yoM);// 46-54 4|3|2
            switch (true) {
                case (isXi1 && isYi1): // 1|4
                    s = isUpPoint(five.xo0, five.yo0, x, y) ? 1 : 4;
                    break;
                case (isXi1 && isYi2):
                    s = 4;
                    break;
                case (isXi1 && isYi3): // 4|3
                    s = isUpPoint(five.xi0, five.yiM, x, y, true) ? 4 : 3;
                    break;
                case (isXi2 && isYi1):
                    s = 1;
                    break;
                case (isXi2 && isYi2):
                    s = 5;
                    break;
                case (isXi2 && isYi3):
                    s = 3;
                    break;
                case (isXi3 && isYi1): // 1|2
                    s = isUpPoint(five.xoN, five.yo0, x, y, true) ? 1 : 2;
                    break;
                case (isXi3 && isYi2):
                    s = 2;
                    break;
                case (isXi3 && isYi3): // 2|3
                    s = isUpPoint(five.xiN, five.yiM, x, y) ? 2 : 3;
                    break;
            }
        } else {
            isXi1 = (x >= four.xo0 && x < four.xiL);
            isXi2 = (x >= four.xiL && x <= four.xiR);
            isXi3 = (x > four.xiR && x <= four.xoN);
            isYi1 = (y >= four.yo0 && y < four.yi);
            isYi2 = (y > four.yi && y <= four.yoM);
            switch (true) {
                case (isXi1 && isYi1): // 1|4
                    s = isUpPoint(four.xo0, four.yo0, x, y) ? 1 : 4;
                    break;
                case (isXi1 && isYi2): // 4|3
                    s = isUpPoint(four.xiL, four.yi, x, y, true) ? 4 : 3;
                    break;
                case (isXi2 && isYi1):
                    s = 1;
                    break;
                case (isXi2 && isYi2):
                    s = 3;
                    break;
                case (isXi3 && isYi1): // 1|2
                    s = isUpPoint(four.xoN, four.yo0, x, y, true) ? 1 : 2;
                    break;
                case (isXi3 && isYi2): // 2|3
                    s = isUpPoint(four.xiR, four.yi, x, y) ? 2 : 3;
                    break;
            }
        }
        return s;
    };

    /**
     * Cкрываем и ховер поверхности и ховер зуба
     */
    me.hideHover = function() {
        var children = Ext.query("div[class*=hover]", me.getEl().dom),
            i, layer;
        for (i=0; i < children.length; i++) {
            layer = new Ext.Element(children[i]);
            if (layer && layer.isDisplayed()) {
                layer.setDisplayed('none');
            }
        }
    };

    /**
     * Показываем ховер поверхности или ховер зуба
     */
    me.showHover = function(pos) {
        var children = Ext.query("div[class*=hover]", me.getEl().dom),
            i, layer;
        for (i=0; i < children.length; i++) {
            layer = new Ext.Element(children[i]);
            if (pos > 0) {
                if (layer.hasClass('hoverSegment') && layer.hasClass('segment' + pos)) {
                    if (!layer.isDisplayed()) layer.setDisplayed('block');
                } else {
                    if (layer.isDisplayed()) layer.setDisplayed('none');
                }
            } else {
                if (layer.hasClass('hoverFull')) {
                    if (!layer.isDisplayed()) layer.setDisplayed('block');
                } else {
                    if (layer.isDisplayed()) layer.setDisplayed('none');
                }
            }
        }
    };

    me.applyData = function(allStates) {
        states = [];
        for (i=0; i < allStates.length; i++) {
            state = allStates[i];
            if (state['ToothStateClass_id'].toString().inlist(['12','13','14','15'])) {
                type = state;
            } else {
                states.push(state);
            }
            if (state['ToothStateClass_id'].toString().inlist(['1','14','15'])) {
                // можно ли ставить состояния поверхностям,
                // если у зуба есть состояние Коронка (10)?
                isAllowEditSurface = false;
            }
        }
        surfacesPositions = [];
        if (isAllowEditSurface) {
            surfacesPositions = ['1','2','3','4'];
        }
        if (isAllowEditSurface && me.isHasFiveSegments()) {
            surfacesPositions.push('5');
        }
        surfaces = {};
        for (i=0; i < surfacesPositions.length; i++) {
            surfaces[surfacesPositions[i]] = defineToothSurfaceTypeId(
                surfacesPositions[i],
                me.isHasFiveSegments(),
                me.isTop(),
                me.isLeft()
            );
        }
        /*
         log(code);
         log(isAllowEditSurface);
         log(surfacesPositions);
         log(surfaces);
         */
    };
    me.isAllowEditSurface = function() {
        return isAllowEditSurface;
    };
    me.getType = function() {
        return type;
    };
    me.getSurfaces = function() {
        return surfaces;
    };
    me.getStates = function() {
        return states || [];
    };
    me.isHasFiveSegments = function() {
        return sw.Promed.StomHelper.ToothMap.isToothHasFiveSegments(sysNum);
    };
    me.getCode = function() {
        return code;
    };
    me.getPosition = function() {
        return position;
    };
    me.getSysNum = function() {
        return sysNum;
    };
    me.getEl = function() {
        return extEl;
    };
};
/**
 * Панель просмотра/редактирования зубной карты
 * @package      libs
 * @access       public
 * @autor		 Alexander Permyakov
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @version      05.2014
 */
sw.Promed.ToothMapPanel = Ext.extend(sw.Promed.Panel, {
    collapsible: false,
    height: 300,
    layout: 'border',
    border: false,
    bodyStyle: 'background-color: #fff; padding: 2px;',

    _params: {
        EvnVizitPLStom_id: null,
        //EvnVizitPLStom_setDate: null,
        Person_id: null
    },
    isLoaded: false,
    _enableEdit: false,
    _isReadOnly: true,
    _hasChanges: false,
    _toothMap: {},
    _toothSurfacePosition: -1,

    applyParams: function(Person_id, EvnVizitPLStom_id, EvnVizitPLStom_setDate) {
        this.setParam('Person_id', Person_id);
        this.setParam('EvnVizitPLStom_id', EvnVizitPLStom_id);
        //this.setParam('EvnVizitPLStom_setDate', EvnVizitPLStom_setDate);
    },
    getParam: function(key) {
        var value = this._params[key] || null;
        if ('EvnVizitPLStom_id' == key && value > 0) {
            var id = this.id.split('_')[1] || null;
            if (id > 0 && id != value) {
                //log('debug: use kostyl');
                value = id;
            }
        }
        return value;
    },
    setParam: function(key, value) {
        this._params[key] = value || null;
    },
    isEnableEdit: function() {
        return this._enableEdit||false;
    },
    setEnableEdit: function(enable) {
        if (this.isReadOnly()) {
            this._enableEdit = false;
        } else {
            this._enableEdit = enable;
        }
        var buttons = this.topToolbar.items;
        if (buttons.items && buttons.items[1]) {// del
            buttons.items[1].setDisabled(!this.isAllowDelete());
        }
    },
    getPrintBtn: function() {
        var buttons = this.topToolbar.items;
        if (buttons.items && buttons.items[0]) {
            return buttons.items[0];
        }
        return null;
    },
    isReadOnly: function() {
        return this._isReadOnly;
    },
    setReadOnly: function(isReadOnly) {
        this._isReadOnly = isReadOnly;
        this.setEnableEdit(!isReadOnly);
    },
    doReset: function() {
        this.setParam('Person_id', null);
        this.setParam('EvnVizitPLStom_id', null);
        //this.setParam('EvnVizitPLStom_setDate', null);
        this.doClear(true);
        this.setReadOnly(true);
    },
    doClear: function(isAll) {
        if (isAll) {
            this.historyStore.removeAll();
            this.historyStore.baseParams = {};
        }
        this.mainViewPanelStore.removeAll();
        this.mainViewPanel.refresh();
    },
    onLoadHistory: function() {
        var me = this;
        /*log({
            debug: 'onLoadHistory',
            panel: me,
            initId: me.getParam('EvnVizitPLStom_id')
        });*/
        if (me.mainViewPanelStore.getCount() == 0) {
            me.historyComboBox.setValue(me.historyStore.baseParams.EvnVizitPLStom_id);
            me.historyComboBox.fireEvent('change', me.historyComboBox, me.historyComboBox.getValue(), null);
        }
    },
    doLoad: function() {
        var me = this;
        /*log({
            debug: 'beforeLoadHistory',
            initId: me.getParam('EvnVizitPLStom_id'),
            mainStoreCnt: me.mainViewPanelStore.getCount(),
            historyStoreCnt: me.historyStore.getCount()
        });*/
        me.historyStore.baseParams.EvnVizitPLStom_id = me.getParam('EvnVizitPLStom_id');
        me.historyStore.baseParams.Person_id = me.getParam('Person_id');
        if (me.historyStore.getCount() == 0) {
            me.historyStore.load({
                scope: me,
                callback: me.onLoadHistory
            });
        } else {
            me.onLoadHistory();
        }
    },
    doPrint: function() {
        window.open('/?c=PersonToothCard&m=doPrint&EvnVizitPLStom_id=' + this.getParam('EvnVizitPLStom_id'), '_blank');
    },
    doDelete: function(options) {
        var me = this, isAll;
        if (!me.getParam('EvnVizitPLStom_id')) {
            return false;
        }
        if (!options) {
            options = {};
        }
        if (!options.callback) {
            options.callback = function(response_obj){};
        }
        isAll = options.withoutLoad || false;
        var loadMask = new Ext.LoadMask(me.getEl(), { msg: "Отмена внесенных изменений..." });
        loadMask.show();
        Ext.Ajax.request({
            params: {
                EvnVizitPLStom_id: me.getParam('EvnVizitPLStom_id')
            },
            url: '/?c=PersonToothCard&m=doRemove',
            failure: function() {
                loadMask.hide();
            },
            success: function(response) {
                loadMask.hide();
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if (response_obj.success) {
                    me.doClear(isAll);
                    if (!isAll) {
                        me.doReloadViewData();
                    }
                    options.callback(response_obj);
                }
            }
        });
        return true;
    },
    doReloadViewData: function() {
        this.doLoadViewData(this.getParam('EvnVizitPLStom_id'));
    },
    doLoadViewData: function(EvnVizitPLStom_id) {
        if (!EvnVizitPLStom_id) {
            return false;
        }
        var me = this,
            params = {
                EvnVizitPLStom_id: EvnVizitPLStom_id
            },
            rec = me.historyStore.getById(EvnVizitPLStom_id),
            index = (rec) ? me.historyStore.indexOf(rec) : -1,
            allowEdit = (0 == index && EvnVizitPLStom_id == me.getParam('EvnVizitPLStom_id'));
        me._hasChanges = false;
        me.setEnableEdit(allowEdit);
        if (me.getPrintBtn()) {
            me.getPrintBtn().setDisabled(true);
        }
        /*log({
            debug: 'beforeLoadViewData',
            index: index,
            initId: me.getParam('EvnVizitPLStom_id'),
            post: params
        });*/
        var loadMask = new Ext.LoadMask(me.getEl(), { msg: "Загрузка зубной карты..." });
        loadMask.show();
        me.mainViewPanelStore.removeAll();
        me.mainViewPanelStore.load({
            params: params,
            scope: me,
            callback: function(){
                loadMask.hide();
                var me = this;
                /*log({
                    debug: 'onLoadViewData',
                    initId: me.getParam('EvnVizitPLStom_id'),
                    historyInitId: me.historyStore.baseParams.EvnVizitPLStom_id
                });*/
                me.isLoaded = true;
                //me.doLayout();
                me.onLoad(me);
            }
        });
        return true;
    },
    onLoad: function(panel){},
    isAllowDelete: function()
    {
        return (this.isEnableEdit() && this.getParam('EvnVizitPLStom_id') > 0);
    },
    /**
     * Показываем форму состояния зуба
     */
    _showToothStateEditForm: function(params)
    {
        var win = getWnd('swPersonToothCardEditWindow');
        if ( win.isVisible() ) {
            sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования состояний зуба уже открыто'));
            return false;
        }
        win.show({
            tooth: params.toothStates,
            ToothStateClassRelation: params.rec.get('ToothStateClassRelation'),
            Person_Age: params.rec.get('Person_Age'),
            callback: params.callback
        });
        return true;
    },
    /**
     * Показываем меню поверхности
     */
    _showToothSurfaceStateEditMenu: function(params)
    {
        var me = this,
            i, cls,
            checkedList = [];
        for(i=0; i < params.toothStates.states.length; i++) {
            cls = params.toothStates.states[i];
            if (cls['ToothSurfaceType_id'] && params.ToothSurfaceType_id == cls['ToothSurfaceType_id']) {
                checkedList.push(cls['ToothStateClass_id']);
            }
        }
        if (me.ToothSurfaceStateEditMenu) {
            me.ToothSurfaceStateEditMenu.destroy();
        }
        me.ToothSurfaceStateEditMenu = new Ext.menu.Menu({
            minWidth: 220
        });
        for (i in params.rec.get('ToothStateClassRelation')) {
            cls = params.rec.get('ToothStateClassRelation')[i];
            if (!cls['OnlyTooth']) {
                me.ToothSurfaceStateEditMenu.addItem(new Ext.menu.CheckItem({
                    canActivate: true,
                    checked: cls['ToothStateClass_id'].toString().inlist(checkedList),
                    itemId: cls['ToothStateClass_id'],
                    text: '<span style="font-weight: bold">' +
                        cls['ToothStateClass_Code'] +
                        '</span> <span>' +
                        cls['ToothStateClass_Name'] +
                        '</span>',
                    handler: function(item){
                        params.onSelect(item.itemId, !item.checked);
                    }
                }));
            }
        }
        me.ToothSurfaceStateEditMenu.showAt(params.e.getXY());
        return true;
    },
    /**
     * Показываем меню поверхности или форму состояния зуба
     */
    editToothState: function(el, e)
    {
        var me = this,
            rec = me.mainViewPanelStore.getAt(0),
            params = {
                e: e,
                rec: rec,
                callback: function(tooth, newStates, canceled) {
                    var data = {
                        EvnVizitPLStom_id: me.getParam('EvnVizitPLStom_id'),
                        PersonToothCard_IsSuperSet: tooth.PersonToothCard_IsSuperSet,
                        ToothPositionType_aid: tooth.ToothPositionType_aid,
                        ToothPositionType_bid: tooth.ToothPositionType_bid,
                        ToothSurfaceType_id: tooth.ToothSurfaceType_id || null,
                        ToothType: tooth.ToothType,
                        states: newStates.toString(),
                        deactivate: canceled.toString(),
                        Tooth_Code: tooth.Tooth_Code // or Tooth_SysNum JawPartType_id
                    };
                    var loadMask = new Ext.LoadMask(me.getEl(), { msg: "Изменение состояний зубной карты..." });
                    loadMask.show();
                    Ext.Ajax.request({
                        params: data,
                        url: '/?c=PersonToothCard&m=doSave',
                        failure: function() {
                            loadMask.hide();
                        },
                        success: function(response) {
                            loadMask.hide();
                            var response_obj = Ext.util.JSON.decode(response.responseText);
                            if (response_obj.success == false) {
                                //sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
                            } else {
                                me.doLoadViewData(me.getParam('EvnVizitPLStom_id'));
                            }
                        }
                    });
                    return true;
                }
            },
            typeState, pos, segments;

        if (!rec || !rec.get('ToothStates')  || !me._toothMap[el.id] ||
            !rec.get('ToothStates')[me._toothMap[el.id].getCode()] ||
            !rec.get('ToothStateClassRelation')
            ) {
            return false;
        }
        params.toothStates = rec.get('ToothStates')[me._toothMap[el.id].getCode()];
        params.toothStates.Tooth_SysNum = me._toothMap[el.id].getSysNum();
        params.toothStates.ToothType = sw.Promed.StomHelper.ToothMap.getDefaultToothType(rec.get('Person_Age'), params.toothStates.Tooth_SysNum);
        typeState = me._toothMap[el.id].getType();
        if (typeState) {
            params.toothStates.ToothType = typeState['ToothStateClass_id'];
        }
        segments = me._toothMap[el.id].getSurfaces();
        pos = me._toothMap[el.id].defineToothSurfacePos(e);
        /*
         log({
             ToothType: params.toothStates.ToothType,
             ToothCode: me._toothMap[el.id].getCode(),
             JawPartCode: me._toothMap[el.id].getJawPartCode(),
             pos: pos,
             surfaces: segments,
             ToothSurfaceType_id: segments[pos],
             isAllowEditSurface: me._toothMap[el.id].isAllowEditSurface()
         });
         */
        if (me._toothMap[el.id].isAllowEditSurface() && segments[pos]) {
            params.ToothSurfaceType_id = segments[pos];
            params.toothStates.ToothSurfaceType_id = segments[pos];
            params.onSelect = function(id, checked) {
                var state,
                    canceled = [],
                    newStates = [];
                state = sw.Promed.StomHelper.ToothMap.hasState(params.toothStates.states, id, true);
                if (state && params.ToothSurfaceType_id != state['ToothSurfaceType_id']) {
                    state = null;
                }
                //log({state: state, id: id, checked: checked});
                // обрабатываем изменения состояний поверхности
                if (!state && checked) {
                    // новое
                    newStates.push(id);
                }
                if (state && !checked) {
                    // отмена
                    canceled.push(state['PersonToothCard_id']);
                }
                params.callback(params.toothStates, newStates, canceled);
            };
            return me._showToothSurfaceStateEditMenu(params);
        } else {
            return me._showToothStateEditForm(params);
        }
    },
    onMouseMove: function(el, e)
    {
        var me = this, pos;
        if (me._toothMap[el.id]) {
            pos = me._toothMap[el.id].defineToothSurfacePos(e);
            // Если изменилось положение курсора относительно поверхности зуба
            if (me._toothSurfacePosition != pos) {
                //Показываем ховер поверхности или ховер зуба
                //log(pos);
                me._toothMap[el.id].showHover(pos);
                me._toothSurfacePosition = pos;
            }
        }
    },
    /**
     * Cкрываем и ховер поверхности и ховер зуба
     */
    hideToothHover: function(el)
    {
        if (this._toothMap[el.id]) {
            this._toothMap[el.id].hideHover();
            this._toothSurfacePosition = -1;
        }
    },
    initComponent: function()
    {
        var me = this;

        me.historyStore = new Ext.data.Store({
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                id: 'EvnVizitPLStom_id'
            }, [{
                mapping: 'EvnVizitPLStom_id',
                name: 'EvnVizitPLStom_id',
                type: 'int'
            }, {
                mapping: 'EvnVizitPLStom_setDT',
                name: 'EvnVizitPLStom_setDT',
                type: 'string'
            }]),
            url: '/?c=PersonToothCard&m=doLoadHistory'
        });

        me.historyComboBox = new Ext.form.ComboBox({
            enableKeyEvents: false,
            editable: false,
            allowBlank: false,// чтобы убрать пустую запись
            //fieldLabel: '',
            emptyText: langs('новая'),
            hideLabel: true,
            forceSelection: true,
            width: 120,
            mode: 'remote',
            //onTrigger2Click: Ext.emptyFn,
            //trigger2Class: 'hideTrigger',
            resizable: false,
            selectOnFocus: false,
            triggerAction: 'all',
            displayField: 'EvnVizitPLStom_setDT',
            hiddenName: 'EvnVizitPLStom_hid',
            valueField: 'EvnVizitPLStom_id',
            store: me.historyStore,
            doBack: function() {
                var oldValue = this.getValue(),
                //index = (oldValue ? this.getStore().indexOfId(oldValue) : 0),
                    index = this.getStore().indexOfId(oldValue),
                    newRec = this.getStore().getAt(index + 1);
                if (newRec) {
                    this.setValue(newRec.get('EvnVizitPLStom_id'));
                    this.fireEvent('change', this, this.getValue(), oldValue);
                }
            },
            doForward: function() {
                var oldValue = this.getValue(),
                //index = (oldValue ? this.getStore().indexOfId(oldValue) : 0),
                    index = this.getStore().indexOfId(oldValue),
                    newRec = this.getStore().getAt(index - 1);
                if (newRec) {
                    this.setValue(newRec.get('EvnVizitPLStom_id'));
                    this.fireEvent('change', this, this.getValue(), oldValue);
                }
            }
        });

        me.historyBackBtn = new Ext.Button({
            handler: function() {
                me.historyComboBox.doBack();
            },
            disabled: true,
            hidden: true,
            hideMode: 'visibility',
            tooltip : langs('К предыдущему состоянию'),
            iconCls: 'back16',
            text: '&nbsp;'
        });
        me.historyForwardBtn = new Ext.Button({
            handler: function() {
                me.historyComboBox.doForward();
            },
            disabled: true,
            hidden: true,
            hideMode: 'visibility',
            tooltip : langs('К следущему состоянию'),
            iconCls: 'forward16',
            style: 'margin-left: 12px',
            text: '&nbsp;'
        });

        me.historyComboBox.addListener({
            change: function(field, newValue, oldValue) {
                var rec = field.getStore().getById(newValue),
                    index = field.getStore().indexOf(rec),
                    prevRec = field.getStore().getAt(index + 1),
                    nextRec = field.getStore().getAt(index - 1);
                if (rec) {
                    me.historyBackBtn.setDisabled(!prevRec);
                    me.historyBackBtn.setVisible(prevRec);
                    me.historyForwardBtn.setDisabled(!nextRec);
                    me.historyForwardBtn.setVisible(nextRec);
                    me.doLoadViewData(rec.get('EvnVizitPLStom_id'));
                }
            }
        });

        me.mainViewPanelStore = new Ext.data.Store({
            autoLoad: false,
            listeners: {
                'load': function() {
                    var rec = me.mainViewPanelStore.getAt(0),
                        isRefresh = me.mainViewPanel.refresh();
                    /*
                     log('mainViewPanelStore on load');// #debug
                     log(rec);// #debug
                     log(me.isEnableEdit());// #debug
                     log(isRefresh); // #debug
                     */
                    if (isRefresh && me.getPrintBtn() && !isMseDepers()) {
                        me.getPrintBtn().setDisabled(false);
                    }
                }
            },
            reader: new Ext.data.JsonReader({
                id: 'Person_id'
            }, [{
                mapping: 'Person_id',
                name: 'Person_id'
            }, {
                mapping: 'Person_SurName',
                name: 'Person_SurName'
            }, {
                mapping: 'Person_FirName',
                name: 'Person_FirName'
            }, {
                mapping: 'Person_SecName',
                name: 'Person_SecName'
            }, {
                mapping: 'Person_BirthDay',
                name: 'Person_BirthDay'
            }, {
                mapping: 'Person_Age',
                name: 'Person_Age'
            }, {
                mapping: 'MedPersonal_Fin',
                name: 'MedPersonal_Fin'
            }, {
                mapping: 'history_date',
                name: 'history_date'
            }, {
                mapping: 'ToothMap',
                name: 'ToothMap'
            }, {
                mapping: 'ToothStates',
                name: 'ToothStates'
            }, {
                mapping: 'ToothStateClassRelation',
                name: 'ToothStateClassRelation'
            }]),
            url: '/?c=PersonToothCard&m=doLoadViewData'
        });

        me.mainViewPanel = new Ext.Panel({
            region: 'center',
            collapsible: false,
            layout: 'fit',
            border: false,
            height: 330,
            autoScroll: true,
            bodyStyle: 'background-color: #fff;',
            refresh : function()
            {
                var rec = me.mainViewPanelStore.getAt(0);
                if (!rec) {
                    this.body.update(langs('<p>Отсутствуют данные зубной карты...</p>'));
                    /*
                     this.removeAll();
                     this.doLayout();
                     this.syncSize();
                     me.doLayout();
                     me.syncSize();*/
                    return false;
                }
                this.updateToothMap(this.body);
                if (me.isEnableEdit()) {
                    var tooth_list = Ext.query("td[class*=toothStates]", this.body.dom),
                        data,
                        i, el;
                    me._toothMap = {};
                    me._toothSurfacePosition = -1;
                    for (i=0; i < tooth_list.length; i++) {
                        el = new Ext.Element(tooth_list[i]);
                        me._toothMap[el.id] = new sw.Promed.Tooth(el);
                        data = rec.get('ToothStates')[me._toothMap[el.id].getCode()];
                        if (data && data.states) {
                            me._toothMap[el.id].applyData(data.states);
                        }
                        el.toothMap = me;
                        el.on('click', function(e){
                            this.toothMap.editToothState(this, e);
                        }, el);
                        el.on('mousemove', function(e){
                            this.toothMap.onMouseMove(this, e);
                        }, el);
                        el.on('mouseout', function(e){
                            this.toothMap.hideToothHover(this);
                        }, el);
                    }
                }
                return true;
            },
            /**
             * Обновляем данные
             */
            updateToothMap: function(body)
            {
                var rec = me.mainViewPanelStore.getAt(0);
                if (!rec) {
                    return false;
                }
                var tpl = new Ext.XTemplate(rec.get('ToothMap')),
                    data = {};
                tpl.overwrite(body, data);
                /*
                 this.removeAll();
                 this.doLayout();
                 this.syncSize();
                 me.doLayout();
                 me.syncSize();*/
                return true;
            }
        });

        me.items = [
            me.mainViewPanel
        ];

        me.tbar = new sw.Promed.Toolbar({
            style: me.topToolbarStyle||'',
            buttons: [{
                handler: function() {
                    me.doPrint();
                },
                iconCls: 'print16',
                text: BTN_GRIDPRINT
            }, {
                handler: function() {
                    me.doDelete();
                },
                iconCls: 'delete16',
                text: BTN_GRIDDEL
            }, '->',
                me.historyBackBtn,
                me.historyComboBox,
                me.historyForwardBtn
            ]
        });

        sw.Promed.ToothMapPanel.superclass.initComponent.apply(this, arguments);

        me.addListener({
            expand: function(panel) {
                panel.doLayout();
            }
        });
    }
});

/**
 * Панель просмотровщика DICOM изображений
 * @package      libs
 * @access       public
 * @autor		 Tokarev Sergey
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @version      12.2014
 */
sw.Promed.DicomViewerPanel = Ext.extend(Ext.Panel, {
	autoHeight: false,
	cls: 'dicomViewerPanel',
	id: 'EUFREF_DicomViewerPanel',
	layout: 'border',
	border: false,
	countPictures: 0,
	loadedPictures: 0,
	activeFrame: 0,
	series: [],
	currentSeries: 0,
	studies: [],
	mode: 'default',
	mouseDrugInfo: {},
	optionsWindows: {},
	drawImageModeFit: 'oneByOne',

	initComponent: function()
	{
		var dViewer = this;
		
		dViewer.addEvents({
			'pressDelButton' : true
		});
		
		dViewer.slider = new Ext.Slider({
			width: 200,
			increment: 1,
			minValue: 0,
			maxValue: 0,
			hidden: true,
			listeners: {
				'change': function(t,n){
					if(dViewer.activeFrame!=n) dViewer.setActiveFrame(n);
				}.createDelegate(this)
			}
		});
		
		var templateForTopToolButtons =  new Ext.Template(
			'<table border="0" cellpadding="0" cellspacing="0" class="x-btn-wrap"><tbody><tr>',
			'<td class="x-btn-center" style="background: none;"><em unselectable="on"><div class="x-btn-text" type="{1}"></div></em></td>',
			"</tr></tbody></table>"
		);
		
		dViewer.topToolbar = new Ext.Toolbar({
			items: [
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Перемещение...'),
					iconCls: 'dicom-toppanel-actions dicom-movebtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.panTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Увеличение'),
					iconCls: 'dicom-toppanel-actions dicom-zoombtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.zoomTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Увеличение фрагмента'),
					iconCls: 'dicom-toppanel-actions dicom-magnifybtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.maginfyTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Яркость/контрастность'),
					iconCls: 'dicom-toppanel-actions dicom-brightnessbtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: false,
					handler: function(t, s){
						dViewer.brightTool(t, s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Поворот по часовой'),
					iconCls: 'dicom-toppanel-actions dicom-rotate-cwbtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: false,
					handler: function(t, s){
						dViewer.rotateDegTool(-90);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Поворот против часовой'),
					iconCls: 'dicom-toppanel-actions dicom-rotate-ccwbtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: false,
					handler: function(t, s){
						dViewer.rotateDegTool(90);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Произвольный поворот'),
					iconCls: 'dicom-toppanel-actions dicom-rotatecustombtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.rotateCustomTool(t,s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Переворот по горизотали'),
					iconCls: 'dicom-toppanel-actions dicom-fliphbtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: false,
					handler: function(t, s){
						dViewer.flipTool(-1,1);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Переворот по вертикали'),
					iconCls: 'dicom-toppanel-actions dicom-flipvbtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: false,
					handler: function(t, s){
						dViewer.flipTool(1,-1);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Размер в окне'),
					iconCls: 'dicom-toppanel-actions dicom-fitbtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: false,
					handler: function(t, s){
						dViewer.fitTool();
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Оригинальный размер'),
					iconCls: 'dicom-toppanel-actions dicom-origfitbtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: false,
					handler: function(t, s){
						dViewer.oneByOneTool();
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Серии исследований'),
					iconCls: 'dicom-toppanel-actions dicom-seriesbtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: false,
					handler: function(t, s){
						if(dViewer.seriesThumbsPanel.collapsed){dViewer.seriesThumbsPanel.expand();}
						else{dViewer.seriesThumbsPanel.collapse();}
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Инструменты видео'),
					iconCls: 'dicom-toppanel-actions dicom-videobtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.setSlider(dViewer.countPictures, s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Изображения'),
					iconCls: 'dicom-toppanel-actions dicom-instansesbtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: true,
					toggleHandler: function(t, s){
						if(s)dViewer.instancesThumbPanel.expand();
						else {dViewer.instancesThumbPanel.collapse(); dViewer.instancesThumbPanel.expandButton.hide();}
					}
				}
			]
		});
		
		//доп тулбар для элементов рисования/комментариев
		dViewer.topCommentsToolbar = new Ext.Toolbar({
			items: [
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Линия'),
					iconCls: 'dicom-toppanel-actions dicom-svgline',
					ctCls: 'lineToolBtn',
					toggleGroup: 'dicomViewerPanelTools',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.lineTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Квадрат'),
					iconCls: 'dicom-toppanel-actions dicom-svgrect',
					toggleGroup: 'dicomViewerPanelTools',
					ctCls: 'rectangleToolBtn',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.rectangleTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Круг'),
					iconCls: 'dicom-toppanel-actions dicom-svgellipse',
					toggleGroup: 'dicomViewerPanelTools',
					ctCls: 'ellipseToolBtn',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.ellipseTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Полилиния'),
					iconCls: 'dicom-toppanel-actions dicom-svgpoliline',
					toggleGroup: 'dicomViewerPanelTools',
					ctCls: 'polilineToolBtn',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.polilineTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Полигон'),
					iconCls: 'dicom-toppanel-actions dicom-svgpoligon',
					toggleGroup: 'dicomViewerPanelTools',
					ctCls: 'poligonToolBtn',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.poligonTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Кривая'),
					iconCls: 'dicom-toppanel-actions dicom-svgcurve',
					toggleGroup: 'dicomViewerPanelTools',
					ctCls: 'curveToolBtn',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.curveTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Закрытая кривая'),
					iconCls: 'dicom-toppanel-actions dicom-svgclosedcurve',
					toggleGroup: 'dicomViewerPanelTools',
					ctCls: 'closedCurveToolBtn',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.closedCurveTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Карандаш'),
					iconCls: 'dicom-toppanel-actions dicom-svgfreehand',
					toggleGroup: 'dicomViewerPanelTools',
					ctCls: 'freehandSplineBtn',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.freehandSplineTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Текст'),
					iconCls: 'dicom-toppanel-actions dicom-svgtext',
					toggleGroup: 'dicomViewerPanelTools',
					ctCls: 'textToolBtn',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.textTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Линейка'),
					iconCls: 'dicom-toppanel-actions dicom-svgruler',
					toggleGroup: 'dicomViewerPanelTools',
					ctCls: 'rulerToolBtn',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.rulerTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Угловая линейка'),
					iconCls: 'dicom-toppanel-actions dicom-svgangleruler',
					toggleGroup: 'dicomViewerPanelTools',
					ctCls: 'rulerAngleToolBtn',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.rulerAngleTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					iconCls: 'dicom-toppanel-actions dicom-svgelements',
					menu: [{
						iconCls: 'dicom-toppanel-actions dicom-svgelementsarrow',
						style: 'padding:0; height: 32px; width: 32px;',
						text: '',
						//text: 'Стрелка',
						handler: function(){
							dViewer.createCustomFigure('arrow');
						}
					},
					{
						iconCls: 'dicom-toppanel-actions dicom-svgelementsstar',
						style: 'padding:0; height: 32px; width: 32px;',
						//text: 'Звезда',
						text: '',
						handler: function(){
							dViewer.createCustomFigure('star');
						}
					},
					{
						iconCls: 'dicom-toppanel-actions dicom-svgelementscross',
						style: 'padding:0; height: 32px; width: 32px;',
						//text: 'Крест',
						text: '',
						handler: function(){
							dViewer.createCustomFigure('cross');
						}
					},
					{
						iconCls: 'dicom-toppanel-actions dicom-svgelementscheck',
						style: 'padding:0; height: 32px; width: 32px;',
						//text: 'Галка',
						text: '',
						handler: function(){
							dViewer.createCustomFigure('check');
						}
					},
					{
						iconCls: 'dicom-toppanel-actions dicom-svgelementsattention',
						style: 'padding:0; height: 32px; width: 32px;',
						//text: 'Внимание',
						text: '',
						handler: function(){
							dViewer.createCustomFigure('attention');
						}
					},
					{
						iconCls: 'dicom-toppanel-actions dicom-svgelementsruler',
						style: 'padding:0; height: 32px; width: 32px;',
						//text: 'Линейка',
						text: '',
						handler: function(){
							dViewer.createCustomFigure('gradline');
						}
					},
					{
						iconCls: 'dicom-toppanel-actions dicom-svgelementscalendar',
						style: 'padding:0; height: 32px; width: 32px;',
						//text: 'Календарь',
						text: '',
						handler: function(){
							dViewer.createCustomFigure('calendar');
						}
					}]
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Текстовая заметка'),
					iconCls: 'dicom-toppanel-actions dicom-svgannotation',
					toggleGroup: 'dicomViewerPanelTools',
					ctCls: 'textAnnotationToolBtn',
					enableToggle: true,
					toggleHandler: function(t, s){
						dViewer.textAnnotationTool(s);
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Очистить все'),
					iconCls: 'dicom-toppanel-actions dicom-svgclearall',
					toggleGroup: 'dicomViewerPanelTools',
					//ctCls: 'textAnnotationToolBtn',
					//enableToggle: true,
					handler: function(t, s){
						dViewer.paper.clear();
						//dViewer.paper.oldViewBox = [0,0,dViewer.getInnerWidth(),dViewer.getInnerHeight()];
						//dViewer.paper.setViewBox(0,0,dViewer.getInnerWidth(),dViewer.getInnerHeight());
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Сохранить'),
					iconCls: 'dicom-toppanel-actions dicom-svgsave',
					toggleGroup: 'dicomViewerPanelTools',
					handler: function(t, s){
						dViewer.paper.saveContent();
					}
				},
				{
					xtype: 'button',
					template: templateForTopToolButtons,
					buttonSelector : "div:first-child",
					tooltip: langs('Настройки'),
					iconCls: 'dicom-toppanel-actions dicom-svgoptions',
					toggleGroup: 'dicomViewerPanelTools',
					handler: function(t, s){
						if(s){
							dViewer.optionsWindows.annotationsSetup.show();
						}
					}
				}
			]
		});
		
		
		//компонент для отображения серий
		dViewer.thumbsView = new Ext.DataView({
			store: new Ext.data.JsonStore({
				autoLoad: false,
				fields: [
					{name: 'seriesDescription', type: 'string'},
					{name: 'modality', type: 'string'},
					{name: 'src', type: 'string'},
					{name: 'seriesNumber', type: 'int'}
				],
				key: 'Org_id'
				//url: C_ORG_LIST
			}),
			tpl: new Ext.XTemplate(
			'<tpl for=".">',
				'<div class="thumb-wrap" style="float: left; margin: 15px; width: 100px; overflow: hidden; cursor: pointer; color: #fff;">',
				'<div class="thumb"><img src="{src}" style="width: 100px; height: 80px;" title="{seriesDescription}"></div>',
				'<span class="x-editable" style="display: block; text-align: center;">{seriesDescription}</span></div>',
			'</tpl>'),
			height: 140,
			multiSelect: false,
			overClass:'dicom-thumb-over',
			selectedClass: 'dicom-thumb-selected',
			itemSelector:'div.thumb-wrap',
			emptyText: 'No images to display',
			listeners: {
				'click' : function( t, index, node, e ){
					var rec = dViewer.thumbsView.store.getAt(index);
						dViewer.resetViewer();
						dViewer.videoPlay(false);
						dViewer.setStudies(dViewer.studies, index);
						dViewer.seriesThumbsPanel.collapse();
				}.createDelegate(dViewer)
			}
		});
		
		dViewer.seriesThumbsPanel = new Ext.Panel({
			autoHeight:true,
			collapsed: true,
			collapsible:true,
			autoScroll: true,
			bodyStyle: 'background: #000; z-index: 999;',
			items: [dViewer.thumbsView]
		});
		
		//компонент для отображения instances
		dViewer.instThumbsView = new Ext.DataView({
			store: new Ext.data.JsonStore({
				autoLoad: false,
				fields: [
					{name: 'smallsrc', type: 'string'},
					{name: 'num', type: 'int'}
				],
				key: 'Org_id'
			}),
			tpl: new Ext.XTemplate(
			'<tpl for=".">',
				'<div class="thumb-wrap" style="margin: 2px; float: left; overflow: hidden; cursor: pointer; color: #fff;">',
				'<div class="thumb"><img src="{smallsrc}" style="width: 50px; background: url(/img/dicomViewer/preloadersmall.gif) no-repeat scroll 50% center rgba(0, 0, 0, 0);"></div>',
				'</div>',
			'</tpl>'),
			multiSelect: false,
			overClass:'dicom-thumb-over',
			itemSelector:'div.thumb-wrap',
			emptyText: 'No images to display',
			selectedClass: 'dicom-thumb-selected',
			listeners: {
				'click' : function( t, index, node, e ){
					var currentSerLooped = dViewer.studies[dViewer.currentSeries].videoLoop,
						currentDataView = dViewer.instThumbsView;
						
					//Косяк экста2.3 - не проставляет класс выделенному элементу по клику, и не записывает в selected выбранную ноду
					//здесь фикс
					currentDataView.selected.removeClass(currentDataView.selectedClass);
					currentDataView.selected.elements = [];
					currentDataView.select(index);
					//конец фикса
					dViewer.videoPlay(false);
					
					if(currentSerLooped){
						dViewer.setImages(dViewer.studies[dViewer.currentSeries], index);
					}
					else{
						dViewer.setActiveFrame(index);
						dViewer.slider.setValue(index);
					}
				}.createDelegate(dViewer)
			}
		});
		
		dViewer.instancesThumbPanel = new Ext.Panel({
			collapsed: false,
			collapsible:true,
			autoScroll: true,
			slideAnchor: 'r',
			width: 190,
			bodyStyle: 'background: #000; z-index: 999;',
			items: [
				dViewer.instThumbsView
			],
			listeners: {
				'expand': function(){
					dViewer.instancesThumbPanel.expandButton.show()
				}.createDelegate(dViewer)
			}
		});
		
		//кнопка закрытия инстансов
		dViewer.instancesThumbPanel.expandButton = new Ext.Button({
			xtype: 'button',
			iconCls: 'dicom-play',
			buttonSelector : "div:first-child",
			template: new Ext.Template(
				'<table border="0" cellpadding="0" cellspacing="0" class="x-btn-wrap" style="position: absolute; top: 45%; z-index: 9999; left: -15px;"><tbody><tr>',
				'<td class="x-btn-center" style="background: none;"><em unselectable="on"><div class="x-btn-text" type="{1}"></div></em></td>',
				"</tr></tbody></table>"
			),
			enableToggle: true,
			handler: function(cmp, st){
				if(st){dViewer.instancesThumbPanel.collapse(); cmp.hide();}
				else{dViewer.instancesThumbPanel.expand()}
			}.createDelegate(dViewer)
		});
		
		//поле фпс для видео
		dViewer.fpsField = new Ext.form.NumberField({
			fieldLabel: 'fps',
			labelStyle: 'color: #fff; text-align: right;',
			value: 15,
			maxValue: 50,
			minValue: 1,
			width: 24,
			allowBlank: false,
			minText: 'minText',
			maxText: 'maxText',
			blankText: 'emptyText',
			listeners: {
				'render': function(c){
					c.el.on('mousewheel', function(e){
						var delta = e.getWheelDelta(),
							val = c.getValue();
							
						c.setValue(val+delta);
						e.preventDefault();
					});
				},
				'invalid': function(c, msg){
					log(msg);
					switch(msg){
						case 'minText': c.setValue(c.minValue); break;
						case 'maxText': c.setValue(c.maxValue); break;
						case 'emptyText': c.setValue(c.minValue); break;
					}
				}
			}
		})
		
		//счетчик текущего кадра
		dViewer.currentFrameLabel = new Ext.form.Label({
			text: langs('1 из 1'),
			style: 'color: #fff; width: 65px; display: block;'
		});
		
		//счетчик загруженных кадров
		dViewer.loadedFramesLabel = new Ext.form.Label({
			html: '<div class="totalframes-loader" style="width: 95px; height: 20px; margin: 0 10px;">'+
				'<div class="totalframes-loader-back" style="left: -95px;"></div>'+
			'</div>',
			setValue: function(num){
				if(this.el){
					var offstLeft = Math.round(95-95*num);
					this.el.child('div.totalframes-loader-back').dom.style = 'left:-'+ offstLeft +'px;'
				}
			}
		});
		
		dViewer.playPauseButton = new Ext.Button({
			//xtype: 'button',
			iconCls: 'dicom-videobutton dicom-play',
			buttonSelector : "div:first-child",
			template: new Ext.Template(
				'<table border="0" cellpadding="0" cellspacing="0" class="x-btn-wrap" style="float: right;"><tbody><tr>',
				'<td class="x-btn-center" style="background: none;"><em unselectable="on"><div class="x-btn-text" type="{1}"></div></em></td>',
				"</tr></tbody></table>"
			),
			enableToggle: true,
			toggleHandler: function(cmp, st){
				var iconEl = cmp.el.child('div.x-btn-text');
				
				if(st){ iconEl.replaceClass('dicom-play', 'dicom-pause'); dViewer.videoPlay(true); }
				else{ iconEl.replaceClass('dicom-pause', 'dicom-play'); dViewer.videoPlay(false); }
			}.createDelegate(dViewer)
		});
		
		Ext.apply(dViewer, {
			items:[
				{
					xtype: 'container',
					region:'north',
					layout: 'anchor',
					autoHeight: true,
					autoEl: {},
					items:[
						dViewer.topToolbar,
						dViewer.topCommentsToolbar,
						dViewer.seriesThumbsPanel
					]
				},
				{
					xtype: 'container',
					region:'east',
					layout: 'anchor',
					height: '100%;',
					autoWidth: true,
					autoEl: {},
					items:[
						dViewer.instancesThumbPanel,
						dViewer.instancesThumbPanel.expandButton
					]
				},
				{
					 xtype: 'container',
					 autoEl: {},
					 region:'center',
					 layout: 'fit',
					 cls: 'dicomViewer-canvaswrapper',
					 id: 'dicomViewer-canvaswrapper',
					 style: 'background-color: #000;'
				},
				{
					xtype: 'container',
					region:'south',
					layout: 'table',
					height: 50,
					id: 'dicom-slidercontainer',
					style: 'z-index: 120;',
					autoEl: {},
					layoutConfig: {
						columns: 3
					},
					items: [
						{
							xtype: 'container',
							layout: 'table',
							colspan: 3,
							height: 10,
							autoEl: {},
							cls: 'dicom-panel-frameInfo',
							style: 'float: right; text-align: right;',
							items:[
								dViewer.loadedFramesLabel,
								dViewer.currentFrameLabel
							]
						},
						{
							xtype: 'container',
							width: 50,
							height: 30,
							autoEl: {},
							items: [
								dViewer.playPauseButton
							]
						},
						dViewer.slider, 
						{
							xtype: 'fieldset',
							labelWidth : 30,
							width: 60,
							height: 30,
							style: 'padding: 10px 0 0; border: none;',
							items :[
								dViewer.fpsField
							]
						}
					]
				}
			]
		});
		
		sw.Promed.DicomViewerPanel.superclass.initComponent.apply(this, arguments);
	},
	
	listeners: {
		'render': function(){
			var dViewer = this,
				elem = dViewer.getEl();
			
			//окно яркости контрастности
			dViewer.optionsWindows.brightnessWin = new Ext.Window({
				modal: false,
				draggable:false,
				resizable:false,
				closable : true,
				brightness: 0,
				contrast: 0,
				closeAction: 'hide', 
				width: 300,
				height: 200,
				title: langs('Яркость/контрастность'),
				layout: 'fit',
				bodyStyle: 'padding: 5px;',
				setBrAndCont: function(){
					var brConWin = this;
						level = Math.pow((brConWin.contrast+100)/100,2);
					
					dViewer.setActiveFrame(dViewer.activeFrame);
					dViewer.imageProcess(function(r,g,b,a){
						r+=brConWin.brightness;
						g+=brConWin.brightness;
						b+=brConWin.brightness;
						return [((r/255-0.5)*level+0.5)*255,((g/255-0.5)*level+0.5)*255,((b/255-0.5)*level+0.5)*255,a];
					});
				},
				bbar: [{
					xtype: 'button',
					text: langs('Применить'),
					handler: function(){
						dViewer.optionsWindows.brightnessWin.hide();
					}.createDelegate(this)
				},
				{
					xtype: 'button',
					text: langs('Отмена'),
					handler: function(){
						dViewer.optionsWindows.brightnessWin.getComponent('sliderOfBrightness').setValue(0);
						dViewer.optionsWindows.brightnessWin.getComponent('sliderOfContrast').setValue(0);
						dViewer.setActiveFrame(dViewer.activeFrame);
					}.createDelegate(this)
				}],
				items: [{
					xtype: 'label',
					text: langs('Яркость:')
				},{
					xtype: 'slider',
					width: 200,
					increment: 1,
					minValue: -100,
					maxValue: 100,
					value: 0,
					id: 'sliderOfBrightness',
					fieldLabel: langs('Яркость:'),
					listeners: {
						'change': function(t,n){
							var brConWin = dViewer.optionsWindows.brightnessWin;

							brConWin.brightness = n;
							brConWin.setBrAndCont();
						}.createDelegate(this)
					}
				},
				{
					xtype: 'label',
					text: langs('Контраст')
				},{
					xtype: 'slider',
					width: 200,
					increment: 1,
					minValue: -100,
					maxValue: 100,
					value: 0,
					id: 'sliderOfContrast',
					fieldLabel: langs('Контраст'),
					listeners: {
						'change': function(t,n){
							var brConWin = dViewer.optionsWindows.brightnessWin;

							brConWin.contrast = n;
							brConWin.setBrAndCont();
						}.createDelegate(this)
					}
				}]
			});
			
			//окно настроек текста
			dViewer.optionsWindows.textOptsWin = new Ext.Window({
				modal: true,
				draggable:false,
				resizable:false,
				closable : true,
				closeAction: 'hide', 
				width: 300,
				height: 200,
				title: langs('Настройки'),
				layout: 'fit',
				bodyStyle: 'padding: 5px;',
				bbar: [{
					xtype: 'button',
					text: 'Ok',
					handler: function(){
						var win = dViewer.optionsWindows.textOptsWin;
							
						win.resetWindow();
						win.hide();
					}.createDelegate(this)
				}],
				
				resetWindow: function(){
					var buttons = dViewer.topCommentsToolbar.items,
						createTextButt = buttons.itemAt(buttons.findIndex('ctCls', 'textToolBtn')),
						createTextAnnotationButt = buttons.itemAt(buttons.findIndex('ctCls', 'textAnnotationToolBtn'));
						
					this.textSize.reset();
					this.textColor.setColor('#000000');
					this.textArea.reset();
					createTextAnnotationButt.toggle(false);
					dViewer.mode = 'default';
					dViewer.paper.createElement = null;
					createTextButt.toggle(false);
				},
				
				initComponent: function()
				{
					var textOpts = this;

					textOpts.textColor = new sw.Promed.colorPicker({
						galleryContainer: textOpts,
						listeners: {
							'changeColor': function(c, clr){
								if(dViewer.mode=='createTextAnnotation'){
									dViewer.paper.createElement[2].attr({
										'fill': clr
									});
								}
								else{
									dViewer.paper.createElement.attr({
										'fill': clr
									});
								}
							}
						}
					});
					
					textOpts.textSize = new Ext.form.ComboBox({
						store: [10,12,14,16,20,24,30,40,100],
						editable: false,
						width: 70,
						mode: 'local',
						triggerAction: 'all',
						emptyText:langs('Размер'),
						selectOnFocus:true,
						listeners: {
							'select': function(index, scrollIntoView){
								if(dViewer.mode=='createTextAnnotation'){
									dViewer.paper.createElement[2].attr({
										'font-size': this.getValue()
									});
									dViewer.paper.setTextAnnotationBlock(dViewer.paper.createElement);
								}
								else{
									dViewer.paper.createElement.attr({
										'font-size': this.getValue()
									});
									if(dViewer.paper.createElement.parentTextAnnotation){
										dViewer.paper.setTextAnnotationBlock(dViewer.paper.createElement.parentTextAnnotation);
									}
								}
							}
						}
					});
					
					textOpts.tbar = [
						textOpts.textSize,
						textOpts.textColor
					];
					
					textOpts.textArea = new Ext.form.TextArea({
						fieldLabel: langs('Текст'),
						name: 'annotationText',
						enableKeyEvents: true,
						listeners: {
							'keyup': function(){
								if(dViewer.mode=='createTextAnnotation'){
									dViewer.paper.createElement[2].attr({
										'text' : textOpts.textArea.getRawValue()
									});
									dViewer.paper.setTextAnnotationBlock(dViewer.paper.createElement);
								}
								else{
									dViewer.paper.createElement.attr({
										'text' : textOpts.textArea.getRawValue()
									});
									if(dViewer.paper.createElement.parentTextAnnotation){
										dViewer.paper.setTextAnnotationBlock(dViewer.paper.createElement.parentTextAnnotation);
									}
								}
							}
						}
					});

					textOpts.items = [
						textOpts.textArea
					];
					
					sw.Promed.DicomViewerPanel.superclass.initComponent.apply(this, arguments);
				},
				listeners: {
					'show': function(c){
						if(dViewer.paper.transformBox){
							dViewer.paper.transformBox.remove();
						}
					},
					'close': function(){
						this.resetWindow();
					},
					'hide': function(){
						this.resetWindow();
					}
				}
			});
			
			//окно настроек svg объектов
			dViewer.optionsWindows.elementsOptsWin = new Ext.Window({
				//modal: true,
				draggable:false,
				resizable:false,
				closable : true,
				closeAction: 'hide', 
				width: 200,
				height: 200,
				title: langs('Настройки'),
				layout: 'form',
				bodyStyle: 'padding: 5px;',
				bbar: [{
					xtype: 'button',
					text: 'Ok',
					handler: function(){
						var win = dViewer.optionsWindows.elementsOptsWin;
							
						win.resetWindow();
						win.hide();
					}.createDelegate(this)
				}],
				
				resetWindow: function(){
					this.strokeSize.reset();
					this.fillColor.setColor('#000000');
					this.strokeColor.setColor('#000000');
					this.opacityValue.reset();
					
					dViewer.mode = 'default';
					dViewer.paper.createElement = null;
				},
				
				initComponent: function()
				{
					var elementOpts = this;

					elementOpts.fillColor = new sw.Promed.colorPicker({
						columnWidth: 100,
						listeners: {
							'changeColor': function(c, clr){
								elementOpts.drawElement.attr({
									'fill': clr
								});
							}
						}
					});
					
					elementOpts.strokeColor = new sw.Promed.colorPicker({
						columnWidth: 100,
						listeners: {
							'changeColor': function(c, clr){
								elementOpts.drawElement.attr({
									'stroke': clr
								});
							}
						}
					});
					
					elementOpts.strokeSize = new Ext.form.ComboBox({
						store: [0,1,2,3,4,6,8,10,12,16],
						editable: false,
						width: 70,
						mode: 'local',
						triggerAction: 'all',
						emptyText:langs('Нет'),
						selectOnFocus:true,
						listeners: {
							'select': function(index, scrollIntoView){
								elementOpts.drawElement.attr({
									'stroke-width': this.getValue()
								});
							}
						}
					});
					
					elementOpts.opacityValue = new Ext.form.ComboBox({
						store: [0,10,20,30,40,50,60,70,80,90,100],
						editable: false,
						width: 70,
						mode: 'local',
						triggerAction: 'all',
						emptyText:langs('Нет'),
						selectOnFocus:true,
						listeners: {
							'select': function(index, scrollIntoView){
								elementOpts.drawElement.attr({
									'opacity': 1-(this.getValue()/100)
								});
							}
						}
					});
					
					elementOpts.fillContainer =  new Ext.Container({
						layout: 'column',
						autoEl: {},
						style: { padding: '2px 0 2px 0'},
						items: [
							{
								xtype: 'label',
								text: langs('Заливка'),
								width: 100
							},
							elementOpts.fillColor
						]
					});
					
					elementOpts.opacityContainer =  new Ext.Container({
						layout: 'column',
						autoEl: {},
						style: { padding: '2px 0 2px 0'},
						items: [
							{
								xtype: 'label',
								text: langs('Прозрачность'),
								width: 100
							},
							elementOpts.opacityValue
						]
					});
					
					elementOpts.strokeColorContainer =  new Ext.Container({
						layout: 'column',
						autoEl: {},
						style: { padding: '2px 0 2px 0'},
						items: [
							{
								xtype: 'label',
								text: langs('Обводка'),
								width: 100
							},
							elementOpts.strokeColor
						]
					});
					
					elementOpts.strokeSizeContainer =  new Ext.Container({
						layout: 'column',
						autoEl: {},
						style: { padding: '2px 0 2px 0'},
						items: [
							{
								xtype: 'label',
								text: langs('Толщина обводки'),
								width: 100
							},
							elementOpts.strokeSize
						]
					});
					
					elementOpts.items = [
						elementOpts.fillContainer,
						elementOpts.opacityContainer,
						elementOpts.strokeColorContainer,
						elementOpts.strokeSizeContainer
					];
					
					sw.Promed.DicomViewerPanel.superclass.initComponent.apply(this, arguments);
				},
				listeners: {
					'show': function(c){
						if(dViewer.paper.transformBox){
							c.drawElement = dViewer.paper.transformBox.parentObj;

							for(var i in c.drawElement.attrs){
								switch(i){
									case 'fill':
										if(c.drawElement.attrs[i] == 'none'){
											c.fillContainer.disable();
										}
										c.fillColor.setColor(c.drawElement.attrs[i]);
										c.fillContainer.enable();
										break;

									case 'stroke':
										c.strokeColor.setColor(c.drawElement.attrs[i]);
										break;

									case 'stroke-width':
										c.strokeSize.setValue(c.drawElement.attrs[i]);
										break;

									case 'opacity':
										c.opacityValue.setValue((1-c.drawElement.attrs[i])*100);
										break;
								}
							}
							dViewer.paper.transformBox.remove();
						}
					},
					'close': function(){
						this.resetWindow();
					},
					'hide': function(){
						this.resetWindow();
					}
				}
			});
			
			//настройки отображения аннотаций
			//store с аннотациями
			dViewer.annotationStore = new Ext.data.JsonStore({
				autoLoad: false,
				storeId: 'annotationStore',
				fields: [
					{name: 'DicomStudyNote_id', type: 'int'},
					{name: 'DicomStudyNote_UID', type: 'string'},
					{name: 'DicomStudyNote_SeriesUID', type: 'string'},
					{name: 'DicomStudyNote_PictureUID', type: 'string'},
					{name: 'DicomStudyNote_XmlData', type: 'string'},
					{name: 'Person_FIO', type: 'string'},
					{name: 'pmUser_insID', type: 'string'},
					{name: 'MedPersonal_id', type: 'string'},
					{name: 'DicomStudyNote_AttachFrames', type: 'string'},
					{name: 'visible', type: 'boolean', defaultValue: 'false'}
				]
			});
			//store с уникальными пользователями
			dViewer.commitUsersStore = new Ext.data.JsonStore({
				autoLoad: false,
				fields: [
					{name: 'Person_FIO', type: 'string'},
					{name: 'pmUser_insID', type: 'string'},
					{name: 'MedPersonal_id', type: 'string'},
					{name: 'visible', type: 'boolean'}
				]
			});
			
			//и эта штука для чекера
			Ext.grid.CheckColumn = function(config){
				Ext.apply(this, config);
				if(!this.id){
					this.id = Ext.id();
				}
				this.renderer = this.renderer.createDelegate(this);
			};
			//и эта штука для чекера
			Ext.grid.CheckColumn.prototype ={
				init : function(grid){
					this.grid = grid;
					this.grid.on('render', function(){
						var view = this.grid.getView();
						view.mainBody.on('mousedown', this.onMouseDown, this);
					}, this);
				},

				onMouseDown : function(e, t){
					if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
						e.stopEvent();								
						var index = this.grid.getView().findRowIndex(t);
						var record = this.grid.store.getAt(index);
						record.set(this.dataIndex, !record.data[this.dataIndex]);
						
						var st = Ext.StoreMgr.lookup('annotationStore');
						var rcollect = st.query( 'MedPersonal_id', record.data.MedPersonal_id);
						rcollect.each(function(r){
							r.set('visible', record.data.visible);
						});

						var dViewer = Ext.getCmp('EUFREF_DicomViewerPanel');
						dViewer.paper.clear();
						var xmlData = dViewer.getCurrentFrameAnnotaton();
						
						if(xmlData)	dViewer.paper.importCompileSVGObjects(xmlData);
						
						if(t.classList.contains('x-grid3-check-col')) {
							//вкл
							t.classList.remove('x-grid3-check-col');
							t.classList.add('x-grid3-check-col-on');
						}
						else{
							//выкл
							t.classList.remove('x-grid3-check-col-on');
							t.classList.add('x-grid3-check-col');
						}
					}
				},

				renderer : function(v, p, record){
					p.css += ' x-grid3-check-col-td'; 
					return '<div class="x-grid3-check-col'+(v?'-on':'')+' x-grid3-cc-'+this.id+'">&#160;</div>';
				}
			};
			
			//эта штука для чекера
			var checkColumn = new Ext.grid.CheckColumn({
			   header: "Отображать",
			   dataIndex: 'visible',
			   width: 85
			});
			
			//грид выбора отображения аннотаций от выбранного пользователя
			dViewer.optionsWindows.annotationsSetupGrid = new Ext.grid.EditorGridPanel({
				store: dViewer.commitUsersStore,
				cm: new Ext.grid.ColumnModel([{
					   id:'pmUser_insID',
					   header: "pmUser_insID",
					   dataIndex: 'pmUser_insID',
					   width: 220,
					   hidden: true
					},{
					   header: "ФИО автора аннотации",
					   dataIndex: 'Person_FIO',
					   width: 230
					},
					{
					   header: "MedPersonal_id",
					   dataIndex: 'MedPersonal_id',
					   width: 120,
					   hidden: true
					},
					checkColumn
				]),
				width:400,
				height:300,
				plugins: [checkColumn],
				listeners: {
					'celldblclick': function(t,ind){
						var rec = t.store.getAt(ind),
							recsCreatedCurrentUser = dViewer.annotationStore.query( 'MedPersonal_id', rec.data.MedPersonal_id);
						
						dViewer.optionsWindows.annotationsDetailSetupGrid.store.removeAll();
						dViewer.optionsWindows.annotationsDetailSetupGrid.store.add(recsCreatedCurrentUser.items);
						dViewer.optionsWindows.annotationsDetailSetupGrid.store.sort('DicomStudyNote_AttachFrames', 'ASC');
						dViewer.optionsWindows.annotationsDetailSetup.show();
					}
				}
			});
			
			//грид настройки отображения аннотаций в интервалах
			dViewer.optionsWindows.annotationsDetailSetupGrid = new Ext.grid.EditorGridPanel({
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{name: 'DicomStudyNote_id', type: 'int'},
						{name: 'DicomStudyNote_UID', type: 'string'},
						{name: 'DicomStudyNote_SeriesUID', type: 'string'},
						{name: 'DicomStudyNote_PictureUID', type: 'string'},
						{name: 'DicomStudyNote_XmlData', type: 'string'},
						{name: 'Person_FIO', type: 'string'},
						{name: 'pmUser_insID', type: 'string'},
						{name: 'MedPersonal_id', type: 'string'},
						{name: 'DicomStudyNote_AttachFrames', type: 'string'},
						{name: 'visible', type: 'boolean', defaultValue: 'false'}
					],
					sortInfo: {field: "DicomStudyNote_AttachFrames", direction: "ASC"}
				}),
				listeners: {
					'afteredit': function(e){
						//console.log('afteredit', e);
						var data = e.record.data;
						
						var params = {
							'DicomStudyNote_id': data.DicomStudyNote_id,
							'study_uid': data.DicomStudyNote_UID,
							'seriesUID': data.DicomStudyNote_SeriesUID,
							'sopIUID': data.DicomStudyNote_PictureUID,
							'canvasXmlData': data.DicomStudyNote_XmlData||'empty',
							'attachFrames': data.DicomStudyNote_AttachFrames
						};
						
						Ext.Ajax.request({
							failure: function(response, options) {
								sw.swMsg.alert(langs('Ошибка'), langs('При сохранении аннотации возникли ошибки'));
							},
							params: params,
							success: function(response, options) {
								if ( response.responseText )
								{
									var result = Ext.util.JSON.decode(response.responseText),
										oldRec = dViewer.annotationStore.query('DicomStudyNote_id', result.DicomStudyNote_id).get(0);

									if(oldRec) oldRec.set('DicomStudyNote_AttachFrames', data.DicomStudyNote_AttachFrames);
								}
							}.createDelegate(this),
							url: '/?c=Dicom&m=saveDicomSvgAnnotation'
						});
					}
				},
				cm: new Ext.grid.ColumnModel([
					{
						id:'DicomStudyNote_id',
						header: "Ид аннотации",
						dataIndex: 'DicomStudyNote_id',
						width: 80
					},{
						header: "DicomStudyNote_UID",
						dataIndex: 'DicomStudyNote_UID',
						width: 130,
						hidden: true
					},
					{
						header: "DicomStudyNote_SeriesUID",
						dataIndex: 'DicomStudyNote_SeriesUID',
						width: 130,
						hidden: true
					},
					{
						header: "DicomStudyNote_PictureUID",
						dataIndex: 'DicomStudyNote_PictureUID',
						width: 130,
						hidden: true
					},
					{
						header: "Изображения",
						dataIndex: 'DicomStudyNote_AttachFrames',
						width: 230,
						sortable: true,
						editor: new Ext.form.TextField({
							allowBlank: false
						})
					}
				]),
				width:400,
				height:300,
				clicksToEdit:1
			});
			
			//окно выбора отображения аннотаций от выбранного пользователя
			dViewer.optionsWindows.annotationsSetup = new Ext.Window({
				modal: false,
				draggable:false,
				resizable:false,
				closable : true,
				closeAction: 'hide', 
				width: 400,
				height: 300,
				title: langs('Настройки аннотаций'),
				layout: 'fit',
				bodyStyle: 'padding: 5px;',
				items: [
					dViewer.optionsWindows.annotationsSetupGrid
				]
			});
			
			//окно настройки отображения аннотаций в интервалах
			dViewer.optionsWindows.annotationsDetailSetup = new Ext.Window({
				modal: false,
				draggable:false,
				resizable:false,
				closable : true,
				closeAction: 'hide', 
				width: 400,
				height: 300,
				title: langs('Настройки интервалов отображения'),
				layout: 'fit',
				bodyStyle: 'padding: 5px;',
				/*bbar: [{
					xtype: 'button',
					text: langs('Применить'),
					handler: function(){
						
					}.createDelegate(this)
				},
				{
					xtype: 'button',
					text: langs('Отмена'),
					handler: function(){
						
					}.createDelegate(this)
				}],*/
				items: [
					dViewer.optionsWindows.annotationsDetailSetupGrid
				]
			});
			
			//обработка событий клавиатуры
			var map = new Ext.KeyMap(this.getEl(), [
				{
					key: [13],
					fn: function() {
						//console.log('enter');
					},
					scope: this
				},
				{
					key: [46],
					fn: function() {
						this.fireEvent("pressDelButton", this);
					},
					scope: this
				}
			]);
		},
		'afterlayout': function(){
			var dViewer = this;

			if(dViewer.boxready){
				
				dViewer.setSlider(dViewer.countPictures);
				
				//событие скролла для элементов серии
				dViewer.seriesThumbsPanel.getEl().on('mousewheel', function(e){
					var delta = e.getWheelDelta()*130;
					
					if(delta>0){dViewer.seriesThumbsPanel.body.scroll('right', delta*130, true);}
					else{dViewer.seriesThumbsPanel.body.scroll('left', -delta*130, true);}
					e.preventDefault();
				});
				
				dViewer.getEcgCanvas();
				dViewer.getAnnotationCanvasEl();
				//установка высоты панели инстансов
				
				//корректировка канвы
				if(document.getElementById('dicomViewer-canvas')){
					document.getElementById('dicomViewer-canvas').height = dViewer.getInnerHeight();
					document.getElementById('dicomViewer-canvas').style.height = dViewer.getInnerHeight()+'px';
					dViewer.setActiveFrame();
				}
				
				dViewer.instancesThumbPanel.setHeight(dViewer.getInnerHeight()-150);
			}
			dViewer.boxready = (typeof(dViewer.boxready)=='undefined');
		}
	},
	//загрузка фреймов в кэш
	initImages: function(images){
		var dViewer = this,
			counerI = 0;
		
		dViewer.series = [];
		dViewer.countPictures = images.length;
		dViewer.loadedPictures = 0;

		for (counerI; counerI< images.length; counerI++){
			var	pic = new Image();
				pic.src = images[counerI].src;
				pic.num = counerI;
				dViewer.series[counerI] = pic;
			
			if(images[counerI].svg){
				pic.svg = images[counerI].svg;
				console.log('images[counerI]', images[counerI])
			}
			
			dViewer.loadedFramesLabel.show();
			pic.onload = function(a) {
				dViewer.loadedPictures++;
				var progressFloat = parseFloat((dViewer.loadedPictures/images.length).toFixed(2));
				dViewer.loadedFramesLabel.setValue(progressFloat);
				if(this.num == 0){ 
					dViewer.oneByOneTool();
					dViewer.setActiveFrame();
				}
				if( dViewer.loadedPictures == images.length )
				{
					dViewer.loadedFramesLabel.hide();
				}
			};
		}
	},
	
	setStudies: function(studiesData, currentSeries){
		var dViewer = this;
		
		dViewer.studies = studiesData;
		dViewer.currentSeries = currentSeries;
		dViewer.setImages(dViewer.studies[dViewer.currentSeries], 0);
		dViewer.setStudiesThumbPanel();
		dViewer.setInstancesThumbPanel();
		if(	dViewer.vMode == 'videoPlay'){
			dViewer.videoPlay(false);
		}
	},
	
	setImages: function(oneSeries, picNum){
		var dViewer = this,
			picts = oneSeries.instances,
			countFrames = picts[picNum].numberOfFrames;
		
		if(countFrames>1){
			picts = [];
			oneSeries.videoLoop = true;
			var firstPic = oneSeries.instances[picNum],
				sliceStr = firstPic.src.slice(0, firstPic.src.length-1);
			
			for(var i=0; i<countFrames; i++){
				picts.push({
					numberOfFrames: 1,
					rows: firstPic.rows,
					src: sliceStr+(i+1),
					svg: (firstPic.svg)?firstPic.svg:null
				});
			}
			dViewer.slider.setValue(0);
		}
		dViewer.initImages(picts);
		dViewer.setSlider(picts.length);
		
		dViewer.loadAnnotations();
	},
	
	//загрузка аннотаций
	loadAnnotations: function(){
		var dViewer = this,
			currentSeries = dViewer.studies[dViewer.currentSeries],
			//currentFrame = currentSeries.instances[dViewer.activeFrame],
			params = {
				'study_uid': currentSeries.studyUID,
				'seriesUID': currentSeries.seriesUID
				//'sopIUID': (currentFrame && currentFrame.numberOfFrames==1)?null:currentSeries.instances[0].sopUID
			};
		
		dViewer.annotationStore.removeAll();
		
		Ext.Ajax.request({
			failure: function(response, options) {
				sw.swMsg.alert(langs('Ошибка'), langs('При загрузке аннотаций возникли ошибки'));
			},
			params: params,
			success: function(response, options) {
				if ( response.responseText )
				{
					var result  = Ext.util.JSON.decode(response.responseText);
					
					dViewer.annotationStore.loadData(result);
					
					if(dViewer.paper){
						if( dViewer.annotationStore.getCount() > 0){
							dViewer.paper.loadContent();
						}
						else{
							dViewer.paper.clear();
						}
					}
				}
			}.createDelegate(this),
			url: '/?c=Dicom&m=loadDicomSvgAnnotation'
		});
	},
	
	//настройка панели отображения списка серий
	setStudiesThumbPanel: function(){
		var dViewer = this,
			studies = dViewer.studies,
			thumbStudies = [];
		
		for(var st in studies){
			var s = studies[st];
			if(typeof(s) == 'object'){
				s.src = s.instances[0].src;
				thumbStudies.push(s);
			}		
		}
		
		dViewer.thumbsView.setWidth(130*thumbStudies.length);
		dViewer.thumbsView.store.loadData(thumbStudies);
	},
	
	//настройка панели отображения списка instances
	setInstancesThumbPanel: function(){
		var dViewer = this,
			studies = dViewer.studies,
			currentSeries = studies[dViewer.currentSeries],
			arrImages = currentSeries.instances,
			thumbInstances = [];
		
		dViewer.instThumbsView.store.removeAll();
		
		for(var n in arrImages){
			var inst = arrImages[n];
			if(typeof(inst) == 'object'){
				inst.smallsrc = arrImages[n].smallsrc;
				inst.num = n;
				dViewer.instThumbsView.store.loadData([inst], true);
			}
		}
	},
	
	resetViewer: function(){
		var dViewer = this,
			canvasEl = dViewer.getCanvas(),
			ctx = canvasEl.getContext('2d');
		
		dViewer.optionsWindows.brightnessWin.getComponent('sliderOfBrightness').setValue(0);
		dViewer.optionsWindows.brightnessWin.getComponent('sliderOfContrast').setValue(0);
		ctx.setTransform(1,0,0,1,0,0);
	},
	
	getCanvas: function(){
		var dViewer = this,
			canvasEl = document.getElementById('dicomViewer-canvas');
		
		if (canvasEl){
			return canvasEl;
		}
		else{
			return dViewer.createCanvasEl();
		}
	},
	
	createCanvasEl: function(w,h){
		var dViewer = this,
			canvasWrapper = dViewer.getEl().child('.dicomViewer-canvaswrapper'),
			canvasElement = document.createElement("canvas");
		
		canvasElement.id = 'dicomViewer-canvas';
		canvasElement.width = dViewer.getInnerWidth();
		canvasElement.height = dViewer.getInnerHeight();
		canvasElement.style.position = 'absolute';
		canvasElement.style.left = '0px';
		canvasElement.style.top = '0px';
		canvasElement.style.width = dViewer.getInnerWidth() + 'px';
		canvasElement.style.height = dViewer.getInnerHeight() + 'px';
		
		canvasWrapper.appendChild(canvasElement);

		var dd = new Ext.dd.DragDrop("dicomViewer-canvas", "ddcanvas");
		dd.onDrag = function(e, id){dViewer.dragFuntion(e);};
		dd.startDrag = function(e){dViewer.startDragFuntion(e);};
		dd.endDrag = function(e){dViewer.endDragFuntion(e);};
		
		//событие скролла
		canvasWrapper.on('mousewheel', function(e){
			var delta = e.getWheelDelta();
			
			dViewer.setActiveFrame(dViewer.activeFrame-delta);
			dViewer.slider.setValue(dViewer.activeFrame);
			e.preventDefault();
		});
		
		return canvasElement;
	},
	
	getCanvasZoomEl: function(){
		var dViewer = this,
			canvasWrapper = dViewer.getEl().child('.dicomViewer-canvaswrapper'),
			canvasZoomEl = document.getElementById('dicomViewer-canvasZoomEl');
			
		if(canvasZoomEl) {
			return canvasZoomEl;
		}

		canvasZoomEl = document.createElement("canvas");
		canvasZoomEl.id = 'dicomViewer-canvasZoomEl';
		canvasZoomEl.width = 200;
		canvasZoomEl.height = 200;
		canvasZoomEl.style = 'border-radius: 100px; box-shadow: 4px 7px 8px black; cursor: none; visibility: hidden;'
		canvasZoomEl.style.position = 'absolute';
		canvasZoomEl.style.width = '200px';
		canvasZoomEl.style.height = '200px';

		canvasWrapper.appendChild(canvasZoomEl);
		return canvasZoomEl;
	},
	
	getEcgCanvas: function(){
		var dViewer = this,
			canvasWrapper = dViewer.getEl().child('.dicomViewer-canvaswrapper'),
			ecgCanvasContainer = document.getElementById('dicomViewer-ecgCanvasContainer');
		
		console.log('rnedered', dViewer.rendered)
		if(!ecgCanvasContainer) {
			var ecgCanvasContainer = document.createElement("div");
			ecgCanvasContainer.id = 'dicomViewer-ecgCanvasContainer';
			ecgCanvasContainer.style.position = 'absolute';
			ecgCanvasContainer.style.zIndex = 99;
			ecgCanvasContainer.setAttribute("tabIndex", "0");
			/*ecgCanvasContainer.height = canvasWrapper.getHeight();
			ecgCanvasContainer.style.height = canvasWrapper.getHeight()+'px';
			ecgCanvasContainer.width = canvasWrapper.getWidth();
			ecgCanvasContainer.style.width = canvasWrapper.getWidth()+'px';*/
			canvasWrapper.appendChild(ecgCanvasContainer);
			
			var ecgPaper = Raphael(ecgCanvasContainer, dViewer.getInnerWidth(), dViewer.getInnerHeight());
		
			dViewer.ecgPaper = ecgPaper;
		}

		return Ext.get('dicomViewer-ecgCanvasContainer');
	},
	
	getAnnotationCanvasEl: function(){
		var dViewer = this,
			canvasWrapper = dViewer.getEl().child('.dicomViewer-canvaswrapper'),
			canvasAnnotationEl = document.getElementById('dicomViewer-canvasAnnotationEl'),
			canv = this.getCanvas(),
			canvContext = canv.getContext("2d");
			
		if(!canvasAnnotationEl) {
			var canvasAnnotationContainer = document.createElement("div");
			canvasAnnotationContainer.id = 'dicomViewer-canvasAnnotationEl';
			canvasAnnotationContainer.style.position = 'absolute';
			canvasAnnotationContainer.style.zIndex = 100;
			canvasAnnotationContainer.setAttribute("tabIndex", "0");
			canvasWrapper.appendChild(canvasAnnotationContainer);
			canvasAnnotationContainerEl = Ext.get('dicomViewer-canvasAnnotationEl');
			
			//отслеживание кликов правых/левых
			canvasAnnotationContainerEl.on('mousedown', function(e){
				e.button == 0?leftMouseClick(e):rightMouseClick(e);
			});
			
			dViewer.on('pressDelButton', function(){
				if(!paper.createElement && paper.transformBox){
					var selectedObj = paper.transformBox.parentObj;
					
					if(selectedObj.parentRuler || selectedObj.parentTextAnnotation){
						(selectedObj.parentRuler)?selectedObj.parentRuler.remove():selectedObj.parentTextAnnotation.remove();
					}
					else{
						selectedObj.remove();
					}
					paper.transformBox.remove();
				}
			});
			
			//убираем меню браузера
			canvasAnnotationContainerEl.on({
				'contextmenu': function() {return false;},
				scope:this,
				preventDefault:true
			});
			
			//драгидроп объекта рисования - на объект канвы
			var dd = new Ext.dd.DragDrop("dicomViewer-canvasAnnotationEl", "ddcanvas"),
				buttons = dViewer.topCommentsToolbar.items,
				createCurveButt = buttons.itemAt(buttons.findIndex('ctCls', 'curveToolBtn')),
				createClosedCurveButt = buttons.itemAt(buttons.findIndex('ctCls', 'closedCurveToolBtn')),
				createPolilineButt = buttons.itemAt(buttons.findIndex('ctCls', 'polilineToolBtn')),
				createPoligonButt = buttons.itemAt(buttons.findIndex('ctCls', 'poligonToolBtn')),
				freehandSplineBtn = buttons.itemAt(buttons.findIndex('ctCls', 'freehandSplineBtn')),
				createTextButt = buttons.itemAt(buttons.findIndex('ctCls', 'textToolBtn')),
				createRulerButt = buttons.itemAt(buttons.findIndex('ctCls', 'rulerToolBtn')),
				createRulerAngleButt = buttons.itemAt(buttons.findIndex('ctCls', 'rulerAngleToolBtn')),
				createTextAnnotationButt = buttons.itemAt(buttons.findIndex('ctCls', 'textAnnotationToolBtn'));
				
			dd.onDrag = function(e, id){dViewer.dragFuntion(e);};
			dd.startDrag = function(e){dViewer.startDragFuntion(e);};
			dd.endDrag = function(e){dViewer.endDragFuntion(e);};
			
			//Raphael starts here
			var paper = Raphael(canvasAnnotationContainer, dViewer.getInnerWidth(), dViewer.getInnerHeight());
			dViewer.paper = paper;
			paper.createElement = null;
			paper.oldViewBox = [0,0,dViewer.getInnerWidth(),dViewer.getInnerHeight()];
			
			//начало
			var leftMouseClick = function (e){
				//ни в коем случае не грохать эту строчку!
				canvasAnnotationContainerEl.focus();
				var localCoords = [e.xy[0]-canvasAnnotationContainerEl.getXY()[0], e.xy[1]-canvasAnnotationContainerEl.getXY()[1]],
					c = null,
					ruler,
					rulerLine,
					rulerTextLength;

				//поправка по трансформации
				if(canvContext.oldTransformation){
					var ot = canvContext.oldTransformation;
					localCoords = [
						(e.xy[0]-canvasAnnotationContainerEl.getXY()[0]-ot[2])/ot[0],
						(e.xy[1]-canvasAnnotationContainerEl.getXY()[1]-ot[3])/ot[1]
					];
				}

				switch (true) {
					case (dViewer.mode=='createCurve' || dViewer.mode=='createClosedCurve'):
						c = paper.createCustomCurve(localCoords, 'T', true);
						c.hasOptions = true;
						dViewer.paper.setDoubleClickable(c);
						(dViewer.mode=='createCurve')?createCurveButt.toggle(true):createClosedCurveButt.toggle(true);
						paper.showTransformBox(c);
						break;

					case (dViewer.mode=='createPoliline' || dViewer.mode=='createPoligon'):
						c = paper.createCustomCurve(localCoords, 'L', false);
						c.hasOptions = true;
						dViewer.paper.setDoubleClickable(c);
						(dViewer.mode=='createPoliline')?createPolilineButt.toggle(true):createPoligonButt.toggle(true);
						paper.showTransformBox(c);
						break;

					case (dViewer.mode=='createFreehandSpline'):
						c = paper.createCustomCurve(localCoords, 'L', false);
						c.hasOptions = true;
						dViewer.paper.setDoubleClickable(c);
						freehandSplineBtn.toggle(true);
						break;

					case (dViewer.mode=='createText'):
						var txtOpts = dViewer.optionsWindows.textOptsWin;
						
						createTextButt.toggle(true);
						c = paper.text(localCoords[0], localCoords[1], "Текст");
						paper.setProperties(c);
						paper.setDraggable(c);
						paper.setClickable(c);
						paper.setDoubleClickable(c);
						c.transformable = true;
						c.hasOptions = true;
						txtOpts.show();
						txtOpts.textArea.setValue(c.attrs.text);
						break;

					case (dViewer.mode=='createRuler'):
						ruler = paper.set();
						rulerLine = paper.createCustomCurve(localCoords, 'L', false).attr({'stroke-width': 1});
						rulerTextLength = paper.text(localCoords[0], localCoords[1], "0px");

						rulerTextLength.attr({
							'fill': '#ff7f04',
							'font-size': 14
						});
						
						ruler.push(rulerLine);
						ruler.push(rulerTextLength);
						ruler.ctype = 'ruler';
						rulerLine.parentRuler = ruler;
						paper.setProperties(ruler);
						paper.setDraggable(ruler);
						paper.setClickable(ruler);
						c = ruler;
						createRulerButt.toggle(true);
						break;

					case (dViewer.mode=='createRulerAngle'):
						rulerLine = paper.createCustomCurve(localCoords, 'L', false).attr({'stroke-width': 1});
						
						if(paper.createElement && paper.createElement[0].parentRuler){
							paper.setRuler(paper.createElement, localCoords);
						}
						else{
							ruler = paper.set();
							rulerTextLength = paper.text(localCoords[0], localCoords[1], "0px");

							rulerTextLength.attr({
								'fill': '#ff7f04',
								'font-size': 14
							});
							
							var angleCurve = paper.createCustomCurve(localCoords, 'L', false); 
							angleCurve.attr({
								"stroke":"#91e842"
							});
							angleCurve.hide();
							var angleCurvetext = paper.text(localCoords[0], localCoords[1], '0'+'°'); 
							angleCurvetext.attr({
								"fill":"#91e842",
								'font-size': 14
							});
							
							angleCurvetext.hide();
							
							ruler.push(rulerLine);
							ruler.push(rulerTextLength);
							
							ruler.push(angleCurve);
							ruler.push(angleCurvetext);
							
							ruler.ctype = 'rulerAngle';
							
							rulerLine.parentRuler = ruler;
							paper.setProperties(ruler);
							paper.setDraggable(ruler);
							paper.setClickable(ruler);
							c = ruler;
						}
						createRulerAngleButt.toggle(true);
						break;

					case (dViewer.mode=='createCustomFigure'):
						c = paper.createElement;
						c.customFigure = true;
						paper.setProperties(c);
						paper.setDraggable(c);
						paper.setClickable(c);
						paper.setDoubleClickable(c);
						c.transformable = true;
						c.hasOptions = true;
						c.translate[0] = localCoords[0];
						c.translate[1] = localCoords[1];
						paper.applyTransforms(c);
						c.show();
						//c = null;
						//paper.createElement = null;
						break;

					case (dViewer.mode=='createTextAnnotation'):
						var textAnnotation = paper.set(),
							line = paper.createCustomCurve(localCoords, 'L', false).attr({'stroke':'#fff','stroke-width': 1}),
							annotationRect = paper.rect(0,0,10,10).hide(),
							annotationText = paper.text(localCoords[0], localCoords[1], 'Текст').attr({'fill': '#000'}).hide(),
							annotationCircle = paper.circle(localCoords[0], localCoords[1],3).attr({'fill': '#fff'});
						
						paper.setDoubleClickable(annotationText);
						annotationText.hasOptions = true;
						
						textAnnotation.push(line);
						textAnnotation.push(annotationRect);
						textAnnotation.push(annotationText);
						textAnnotation.push(annotationCircle);
						
						textAnnotation.ctype = 'annotationText';
						line.parentTextAnnotation = textAnnotation;
						annotationText.parentTextAnnotation = textAnnotation;
						paper.setProperties(textAnnotation);
						paper.setDraggable(textAnnotation);
						paper.setClickable(textAnnotation);
						paper.showTransformBox(line);
						c = textAnnotation;
						createTextAnnotationButt.toggle(true);
						break;
				}
				if (c) {
					paper.createElement = c;
				}
			};
			
			//правый клик
			//завершение начатых дел
			var rightMouseClick = function (e){
				if(paper.splineHandler){paper.splineHandler.remove();}
				if(paper.transformBox){paper.transformBox.remove();}
				switch (dViewer.mode){
					case 'createClosedCurve':
						var trPath = paper.createElement.attrs.path+'z';
						paper.createElement.attr({
							'path': trPath,
							'fill': "#61c419"
						});
						createClosedCurveButt.toggle(false);
						paper.showTransformBox(paper.createElement);
						break;

					case 'createCurve':
						createCurveButt.toggle(false);
						paper.showTransformBox(paper.createElement);
						break;

					case 'createPoliline':
						createPolilineButt.toggle(false);
						break;

					case 'createPoligon':
						var trPath = paper.createElement.attrs.path+'z';
						paper.createElement.attr({
							'path': trPath,
							'fill': "#61c419"
						});
						createPoligonButt.toggle(false);
						paper.showTransformBox(paper.createElement);
						break;

					case 'createFreehandSpline':
						freehandSplineBtn.toggle(false);
						break;

					case 'createRulerAngle':
						createRulerAngleButt.toggle(false);
						break;

					case 'createTextAnnotation':
						createTextAnnotationButt.toggle(false);
						break;
				}
				
				dViewer.mode = 'default';
				paper.createElement = null;
				dViewer.setCursorOnCanvas('default');
				dViewer.topToolbar.items.each(function(el){
					el.toggle(false, true);
				});
			};
			
			//настройка элемента кликабельным
			paper.setClickable = function(c){
				c.click(function(){
					dViewer.mode = 'default'; 
					dViewer.setCursorOnCanvas('default');
					dViewer.topToolbar.items.each(function(el){
						el.toggle(false, true);
					})
					if(c.transformable){paper.showTransformBox(c)}
					if(paper.splineHandler){paper.splineHandler.remove();}
					if(c.ctype=='annotationText'){paper.showTransformBox(c[0]);}
				})
			};

			paper.setDoubleClickable = function(c){
				var txtOpts = dViewer.optionsWindows.textOptsWin,
					elOpts = dViewer.optionsWindows.elementsOptsWin,
					buttons = dViewer.topCommentsToolbar.items,
					createTextButt = buttons.itemAt(buttons.findIndex('ctCls', 'textToolBtn'));

				c.dblclick(function(e,d,f, a, b, w){
					if(c.hasOptions){
						if(c.type == 'text'){
							dViewer.mode='createText';
							dViewer.paper.createElement = c;
							createTextButt.disabled = false;
							createTextButt.toggle(true);
							txtOpts.show();
							txtOpts.textArea.setValue(c.attrs.text);
							txtOpts.textSize.setValue(c.attrs['font-size']);
							txtOpts.textColor.setColor(c.attrs.fill);
						}
						else{
							elOpts.show();
							//return false;
						}
					}
				})
			};
			//настройка элемента трансформируемым
			paper.setProperties = function(elem){
				elem.translate = [ 0, 0 ];
				elem.scale = [ 1, 1 ];
				elem.rotate = 0; 
				return elem;
			};
			//применение трансформа
			paper.applyTransforms = function(elem) {
				if(typeof(elem.rotate)=="function"){elem.rotate=0}
				var strng = "t" + elem.translate[0] + "," + elem.translate[1] + "r"
						+ elem.rotate + "s" + elem.scale[0] + "," + elem.scale[1];
				elem.transform(strng);
				elem.osx = elem.scale[0];
				elem.osy = elem.scale[1];
			};
			//применение трансформа для кривой
			paper.applyTransformsString = function(cmp) {
				var strng = "t" + cmp.curvTranslate[0] + "," + cmp.curvTranslate[1] + "r"
					+ cmp.rotate + "s" + cmp.curvScale[0] + "," + cmp.curvScale[1],
					trPath = Raphael.transformPath(cmp.attrs.path, strng);
				
				cmp.osx = cmp.scale[0];
				cmp.osy = cmp.scale[1];
				cmp.ort = cmp.rotate;
				cmp.attr({
					'path': trPath
				});
				return trPath;
			};
			
			//применение драгидропа для объектов raphael
			paper.setDraggable = function(c)
			{
				c.drag(
					function(dx, dy, x,y,e){
						//console.log('move', dx, c.odx, x);
						
						//поправка по текущим трансформациям
						if(canvContext.oldTransformation){
							dx /=canvContext.oldTransformation[0];
							dy /=canvContext.oldTransformation[1];
						}
						
						if(paper.createElement){return false}
						var deltaX = (c.odx + dx)-c.translate[0],
							deltaY = (c.ody + dy)-c.translate[1];
						
						c.translate[0] = c.odx + dx;
						c.translate[1] = c.ody + dy;

						if(c.transformable){
							if(paper.transformBox)paper.transformBox.remove();
							if(paper.splineHandler){paper.splineHandler.remove();}
						}
						//если это элемент трансформации
						if(paper.transformBox && ((paper.transformBox.items.indexOf(c) != -1))){
							transformElementByHandler(c, paper.transformBox.items.indexOf(c), deltaX, deltaY);
						}
						//если редактируем кривую
						if(paper.splineHandler && ((paper.splineHandler.items.indexOf(c) != -1))){
							paper.transformSpline(c, deltaX, deltaY, paper.splineHandler.items.indexOf(c));
						}
						paper.applyTransforms(c);
					},
					function(x,y,e){
						//console.log('start', x,y,e);
						dViewer.topToolbar.items.each(function(el){
							el.toggle(false, true);
						});
						dViewer.mode = 'default'; 
						dViewer.setCursorOnCanvas('default');
						
						if(c.transformable){
							if(paper.transformBox)paper.transformBox.remove();
							if(paper.splineHandler){paper.splineHandler.remove();}
						}
						c.odx = c.translate[0];
						c.ody = c.translate[1];
						c.attr({
							"cursor":"move"
						});
					},
					function(){
						//console.log('end');
						if(paper.createElement){return false}
						if(c.transformable){
							paper.showTransformBox(c);
						}
						if(paper.transformBox && ( (paper.transformBox.items.indexOf(c) != -1)&&(paper.transformBox.items.indexOf(c) < 6) )){
							copyTransformMatrix(paper.transformBox[0], paper.transformBox.parentObj);
							paper.applyTransforms(paper.transformBox.parentObj);
						}
						c.attr({
							"cursor":"default"
						});
					}
				)
			};
			
			paper.removeTransformSplineHandlers = function(trBox){
				for (var i=6; i<trBox.length; i++){
					trBox[i].remove();
				}
			};
			
			paper.removeTransformHandlers = function(trBox){
				for (var i=0; i<6; i++){
					trBox[i].hide();
				}
			};
			
			//создание кривой по параметрам
			paper.createCustomCurve = function(localCoords, typePoint, convertToCtmlr){
				if (!paper.createElement){
					c = paper.path('M '+localCoords[0]+','+localCoords[1]+' L '+localCoords[0]+','+ localCoords[1]);
					c.attr({
						"stroke":"#a7c7dc",
						"cursor":"default",
						"stroke-width": 3
					});
					c.transformable = true;
					paper.setProperties(c);
					paper.setDraggable(c);
					paper.setClickable(c);
				}
				else{
					c = paper.createElement;
					if(dViewer.mode=='createRulerAngle'||dViewer.mode=='createTextAnnotation'){	c = paper.createElement[0];}
					var trPath = c.attrs.path;
					if(trPath[0][1]==trPath[1][1] && trPath[0][2]==trPath[1][2]){trPath.pop();}
					trPath.push([typePoint, localCoords[0], localCoords[1]]);
					if(trPath.length>2 && convertToCtmlr){
						trPath = paper.convertPathToCatmullrow(trPath);
					}
					c.attr({
						'path': trPath
					});
				}
				return c;
			};
			
			//конвертация пути из формата svg в сatmull-row
			paper.convertPathToCatmullrow = function(path){
				var ctrPath = '';
				for(i=0; i<path.length; i++){
					switch (i){
						case 0:
							ctrPath+=(path[i][0]+' '+path[i][1]+','+path[i][2]+' ');
							break;

						case 1:
							if(path[i][0]=='C'){ctrPath+=('R '+path[i][5]+' '+path[i][6]);}
							else{ctrPath+=('R '+path[i][1]+' '+path[i][2]);}
							break;

						default: {
							if(path[i][0]=='C'){ctrPath+=(' '+path[i][5]+' '+path[i][6]);}
							else{ctrPath+=(' '+path[i][1]+' '+path[i][2]);}
							break;
						}
					}
				}
				return ctrPath;
			};
			
			//настройка текстового блока аннотации
			paper.setTextAnnotationBlock = function(c){
				var linePathEl = c[0],
					pointConnection = linePathEl.attrs.path[1],
					textBox = c[2].getBBox(),
					leftDelta = linePathEl.attrs.path[1][1]-linePathEl.attrs.path[0][1],
					rightDelta = linePathEl.attrs.path[1][2]-linePathEl.attrs.path[0][2],
					padding = 5,
					offsets = [
						leftDelta<0?(-textBox.width):(textBox.width),
						rightDelta<0?(-textBox.height):(textBox.height)
					];
				
				c[3].attr({
					'cx': linePathEl.attrs.path[0][1],
					'cy': linePathEl.attrs.path[0][2]
				});
				
				c[1].attr({
					'x': pointConnection[1]+(offsets[0]>0?0:offsets[0]-padding*2),
					'y': pointConnection[2]+(offsets[1]>0?0:offsets[1]-padding*2),
					'width': textBox.width+padding*2,
					'height': textBox.height+padding*2,
					'fill': '#ffffff',
					'opacity': 0.5
				});
				
				c[2].attr({
					'x': c[1].attrs.x+padding+textBox.width/2,
					'y': c[1].attrs.y+padding+textBox.height/2
				});
			};
			
			paper.setRuler = function(c, coords){
				var rulerPathEl = c[0],
					rulerLength = Raphael.getTotalLength(rulerPathEl.attrs.path),
					degreeLength = 50,
					countDegrees = Math.floor(rulerLength/degreeLength);
				
				c[1].attr({
					'text': rulerLength.toFixed(3)+'px',
					'x': coords[0],
					'y': 20+coords[1]
				});
				
				//уголок
				if(rulerPathEl.attrs.path.length>2){
					var cornerCoords = [rulerPathEl.attrs.path[1][1], rulerPathEl.attrs.path[1][2]],
						prevPointCoords = [rulerPathEl.attrs.path[0][1], rulerPathEl.attrs.path[0][2]],
						nextPointCoords = [rulerPathEl.attrs.path[2][1], rulerPathEl.attrs.path[2][2]],
						interval = 10,
						getCoordinatesNearPoint = function(fromX, fromY, toX, toY, length){
							var xpoint = fromX + length * (toX-fromX) / Math.sqrt( Math.pow((toX-fromX), 2) + Math.pow((toY - fromY),2) ),
								ypoint = fromY + length * (toY-fromY) / Math.sqrt( Math.pow((toX-fromX),2) + Math.pow((toY - fromY),2) );
							return ([xpoint, ypoint]);
						},
						startAngleArcCoords = getCoordinatesNearPoint(cornerCoords[0], cornerCoords[1], prevPointCoords[0], prevPointCoords[1], interval),
						endAngleArcCoords = getCoordinatesNearPoint(cornerCoords[0], cornerCoords[1], nextPointCoords[0], nextPointCoords[1], interval),
						deltax = (endAngleArcCoords[0]-startAngleArcCoords[0]),
						deltay = (endAngleArcCoords[1]-startAngleArcCoords[1]),
						raphAngle = Raphael.angle( endAngleArcCoords[0], endAngleArcCoords[1], startAngleArcCoords[0], startAngleArcCoords[1], cornerCoords[0], cornerCoords[1]),
						angle = (raphAngle<0)?(raphAngle+=360):raphAngle,
						cutAngle = (angle<180)?(angle):(360-angle),
						deltaTextOne = getCoordinatesNearPoint(cornerCoords[0], cornerCoords[1], prevPointCoords[0], prevPointCoords[1], interval*5),
						deltaTextTwo = getCoordinatesNearPoint(cornerCoords[0], cornerCoords[1], nextPointCoords[0], nextPointCoords[1], interval*5),
						textAnglepos = getCoordinatesNearPoint(cornerCoords[0], cornerCoords[1], (deltaTextOne[0]+deltaTextTwo[0])/2, (deltaTextOne[1]+deltaTextTwo[1])/2, 40);

					c[2].attr({
						'path': 'M'+startAngleArcCoords[0]+' '+startAngleArcCoords[1]+' a10 10 0 0 '+((angle<180)?1:0)+' '+deltax+' '+deltay
					});

					c[3].attr({
						'text': cutAngle.toFixed(2)+'°',
						'x': textAnglepos[0], 
						'y': textAnglepos[1],
						'text-anchor': 'middle'
					});
					c[2].show(); c[3].show();
				}
				
				//риски
				for(var i=0; i<=countDegrees; i++){
					var pointDeg = c[0].getPointAtLength(i*degreeLength),
						rad=(pointDeg.alpha+45)*Math.PI/180,
						dx=5*Math.cos(rad)-5*Math.sin(rad),
						dy=5*Math.sin(rad)+5*Math.cos(rad),
						ddx=pointDeg.x,
						ddy=pointDeg.y,
						degLine;
				
					switch(c.ctype){
						case 'ruler':
							degIndex = 2;
							break;

						case 'rulerAngle':
							degIndex = 4;
							break;
					}
					
					if(c[i+degIndex] && c[i+degIndex].type!=null){
						if(countDegrees<(c.length-(degIndex+1))){
							c.pop().remove();
						}
						degLine = c[i+degIndex];
						degLine.attr({
							'path':  'M '+(ddx-dx)+' '+(ddy-dy)+' L '+(ddx+dx)+' '+(ddy+dy),
							'stroke': '#a7c7dc'	
						})
					}
					else{
						degLine = paper.path( 'M '+(ddx-dx)+' '+(ddy-dy)+' L '+(ddx+dx)+' '+(ddy+dy) ).attr({'stroke': '#a7c7dc'});
						
						paper.setProperties(degLine);
						degLine.translate = c.translate;
						paper.applyTransforms(degLine);
						c.push(degLine);
					}
				}
			};
			
			//трансформация трансформбокса его элементами(угловые точки, поворот, узлы пути)
			function transformElementByHandler(c, index, deltaX, deltaY){
				var parentObj = paper.transformBox.parentObj,
					trBox = paper.transformBox[0],
					rad=trBox.rotate*Math.PI/180,
					dx=deltaX*Math.cos(rad)+deltaY*Math.sin(rad),
					dy=-deltaX*Math.sin(rad)+deltaY*Math.cos(rad),
					xscaleDelta, yscaleDelta;
					
				switch( index ) {
					case 0:
						break;

					case 1:
						xscaleDelta = trBox.osx-dx/trBox.attrs.width;
						yscaleDelta = trBox.osy-dy/trBox.attrs.height;
						break;

					case 2:
						xscaleDelta = trBox.osx+dx/trBox.attrs.width;
						yscaleDelta = trBox.osy-dy/trBox.attrs.height;
						break;

					case 3:
						xscaleDelta = trBox.osx+dx/trBox.attrs.width;
						yscaleDelta = trBox.osy+dy/trBox.attrs.height;
						break;

					case 4:
						xscaleDelta = trBox.osx-dx/trBox.attrs.width;
						yscaleDelta = trBox.osy+dy/trBox.attrs.height;
						break;

					case 5:
						var bboxCoords = trBox.getBBox(true),
							xy0 = [trBox.matrix.x(bboxCoords.x+bboxCoords.width/2, bboxCoords.y+bboxCoords.height), trBox.matrix.y(bboxCoords.x+bboxCoords.width/2, bboxCoords.y+bboxCoords.height)],
							xy1 = paper.transformBox[index].translate,
							angle = Raphael.angle(xy0[0],xy0[1],xy1[0], xy1[1])-90;

						trBox.rotate = angle;
						paper.removeTransformSplineHandlers(paper.transformBox);
						break;
				}
				
				switch( true ) {
					case (index<5):
						paper.removeTransformSplineHandlers(paper.transformBox);
						trBox.scale = [xscaleDelta, yscaleDelta];
						trBox.translate = [trBox.translate[0]+deltaX/2,trBox.translate[1]+deltaY/2];
						break;

					//здесь забронированы места для точек кривых
					case (index>5):
						var parentPath = parentObj.attrs.path,
							nodeCoords = parentPath[index-6],
							newNodeCoordX = nodeCoords[1]+dx,
							newNodeCoordY = nodeCoords[2]+dy;
							
						paper.removeTransformHandlers(paper.transformBox);
						
						switch (nodeCoords[0]){
							case "L":
								parentPath[index-6] = [nodeCoords[0], newNodeCoordX, newNodeCoordY]; 
								break;

							case "M":
								parentPath[index-6] = [nodeCoords[0], newNodeCoordX, newNodeCoordY]; 
								break;

							case "T":
								parentPath[index-6] = [nodeCoords[0], newNodeCoordX, newNodeCoordY]; 
								break;

							case "C":
								parentPath[index-6][5] = nodeCoords[5]+dx;
								parentPath[index-6][6] = nodeCoords[6]+dy;
								break;
						}
						parentObj.attr({
							'path': parentPath
						});
						
						//если редактируем кривую которая является частью линейки
						if(parentObj.parentRuler){paper.setRuler(parentObj.parentRuler, [newNodeCoordX, newNodeCoordY]);}
						if(parentObj.parentTextAnnotation){paper.setTextAnnotationBlock(parentObj.parentTextAnnotation, [newNodeCoordX, newNodeCoordY], index);}
						break;
				}
				paper.applyTransforms(trBox);
				setTransformElementsToTransformBox(trBox);
			}
			
			//копирование свойств объекта
			var copyTransformMatrix = function(fromObj, toObj){
				var transformParams = fromObj.matrix.split();
				toObj.transform(fromObj.matrix.toTransformString());
				toObj.rotate = fromObj.rotate;
				toObj.translate = fromObj.translate;
				toObj.osx = transformParams.scalex;
				toObj.osy = transformParams.scaley;
				toObj.scale = fromObj.scale;
			};
			
			//применение трансформации элемента согласно трансформбоксу
			var setTransformElementsToTransformBox = function(c){
				var bboxCoords = c.getBBox(true),
					transformBox = paper.transformBox;
				for(var i=1; i<6; i++){
					var tNode = transformBox[i],
						marginX = tNode.attrs.width,
						marginY = tNode.attrs.height,
						rad=c.rotate*Math.PI/180,
						dx=Math.sin(rad)+Math.cos(rad),
						dy=Math.sin(rad)-Math.cos(rad),
						matr = c.matrix.split(),
						scaleDeltaX = c.osx?c.osx:matr.scalex,
						scaleDeltaY = c.osy?c.osy:matr.scaley,
						ddx,
						ddy;

					switch (i) {
						case 1:
							ddx = (dx<0?0:-dx);
							ddy = (dy>0?0:dy);
						
							tNode.translate[0] = c.matrix.x(bboxCoords.x+marginX*ddx/scaleDeltaX, bboxCoords.y+marginY*ddy/scaleDeltaY);
							tNode.translate[1] = c.matrix.y(bboxCoords.x+marginX*ddx/scaleDeltaX, bboxCoords.y+marginY*ddy/scaleDeltaY);
							break;

						case 2:
							ddx = (dx>0?0:-dx);
							ddy = (dy>0?0:dy);

							tNode.translate[0] = c.matrix.x(bboxCoords.x+bboxCoords.width+marginX*ddx/scaleDeltaX, bboxCoords.y+marginY*ddy/scaleDeltaY);
							tNode.translate[1] = c.matrix.y(bboxCoords.x+bboxCoords.width+marginX*ddx/scaleDeltaX, bboxCoords.y+marginY*ddy/scaleDeltaY);	
							break;

						case 3:
							ddx = (dx>0?0:-dx);
							ddy = (dy<0?0:dy);
							
							tNode.translate[0] = c.matrix.x(bboxCoords.x+bboxCoords.width+marginX*ddx/scaleDeltaX, bboxCoords.y+bboxCoords.height+marginY*ddy/scaleDeltaY);
							tNode.translate[1] = c.matrix.y(bboxCoords.x+bboxCoords.width+marginX*ddx/scaleDeltaX, bboxCoords.y+bboxCoords.height+marginY*ddy/scaleDeltaY);
							break;

						case 4:
							ddx = (dx<0?0:-dx);
							ddy = (dy<0?0:dy);

							tNode.translate[0] = c.matrix.x(bboxCoords.x+marginX*ddx/scaleDeltaX, bboxCoords.y+bboxCoords.height+marginY*ddy/scaleDeltaY);
							tNode.translate[1] = c.matrix.y(bboxCoords.x+marginX*ddx/scaleDeltaX, bboxCoords.y+bboxCoords.height+marginY*ddy/scaleDeltaY);
							break;

						case 5:
							tNode.translate[0] = c.matrix.x(bboxCoords.x+bboxCoords.width/2, bboxCoords.y-tNode.attrs.r*4/scaleDeltaY);
							tNode.translate[1] = c.matrix.y(bboxCoords.x+bboxCoords.width/2, bboxCoords.y-tNode.attrs.r*4/scaleDeltaY);
							break;
					}
					paper.applyTransforms(transformBox[i]);
				}
			}
			
			//создание элементов для манипуляции трансформации
			//функция отображения трансформбокса
			paper.showTransformBox = function(c){
				var transformBoxRect = paper.rect(0, 0, 0, 0),
					bboxCoords = c.getBBox(true);
				
				if(paper.transformBox){paper.transformBox.remove();}
				//создание стандартных элементов управления
				transformBoxRect.attr({
					"stroke":"#E50D0D",
					"stroke-dasharray": "- "
				});
				paper.setProperties(transformBoxRect);
				paper.transformBox = paper.set();
				
				paper.transformBox.push(transformBoxRect);
				for(var i=0; i<5; i++){
					var transEl;
					if(i<4){
						transEl = paper.rect(0, 0, 10, 10);
						transEl.attr({
							"fill":"#0B65BF",
							"stroke":"#1E5799"
						});
					}
					else{
						transEl = paper.circle(0, 0, 10);
						transEl.attr({
							"fill":"#0B65BF",
							"stroke":"#BCBCBC"
						});
					}
					paper.setProperties(transEl);
					paper.setDraggable(transEl);
					paper.transformBox.push(transEl);
				}
				paper.transformBox.hide();
				paper.transformBox.parentObj = c;
				//настройка аттрибутов трансформбокса от трансф. элемента
				switch(c.type){
					case 'rect':
						paper.transformBox[0].attr({
							'x':c.attrs.x,
							'y':c.attrs.y,
							'width':c.attrs.width,
							'height':c.attrs.height
						});
						break;

					case 'ellipse':
						paper.transformBox[0].attr({
							'x':c.attrs.cx-c.attrs.rx,
							'y':c.attrs.cy-c.attrs.ry,
							'width':c.attrs.rx*2,
							'height':c.attrs.ry*2
						});
						break;

					case 'path':
						var coords = Raphael.pathBBox(c.attrs.path);
						paper.transformBox[0].attr({
							'x':coords.x,
							'y':coords.y,
							'width':coords.width,
							'height':coords.height
						});
						break;

					case 'text':
						paper.transformBox[0].attr({
							'x':bboxCoords.x,
							'y':bboxCoords.y,
							'width':bboxCoords.width,
							'height':bboxCoords.height
						});
						break;
				}
				paper.transformBox[0].transform(c.matrix.toTransformString());

				copyTransformMatrix(c, paper.transformBox[0]);
				setTransformElementsToTransformBox(c);
				
				//для кривых простых не отображать стандартные элементы управления
				if(c.type=='path' && !c.customFigure){
					paper.showPathNodes(c);
					(!paper.createElement && c.attrs.path.length>2 && (!c.parentRuler))?paper.transformBox.show():paper.removeTransformHandlers(paper.transformBox);
				}
				else{paper.transformBox.show();}
				paper.transformBox.toFront();
			};
			
			//поворот холста и отображение
			paper.rotateAndFlipCanvasSVG = function(angle, flipX, flipY){
				var	group = paper.set(),
					skipSetsCount = 0,
					countElements = 0,
					pathTransfGroupRotate = '';
				
				if(paper.rotateElementHelper){
					paper.rotateElementHelper.remove();
				}
				
				//collect элементы, сеты
				paper.forEach(function(e, i){
					//отслеживаем сеты
					if(skipSetsCount==0){
						if(e.parentRuler){
							//отслеживаем линейки (ruler)
							countElements = e.parentRuler.length;
							skipSetsCount = countElements;
						}
						if(e.parentTextAnnotation){
							//отслеживаем комменты
							countElements =  e.parentTextAnnotation.length;
							skipSetsCount = countElements;
						}
					}

					if( skipSetsCount == 0 || skipSetsCount == countElements){
						group.push(e);
						var bbx = e.getBBox();
						var bbxCenter = [bbx.x+bbx.width/2, bbx.y+bbx.height/2];
						if(group.length==1){ pathTransfGroupRotate += 'M '+bbxCenter[0]+','+bbxCenter[1]; }
						else { pathTransfGroupRotate += ' L '+bbxCenter[0]+','+bbxCenter[1]; }
					}
					(skipSetsCount>0)?skipSetsCount -=1:true;
				});
				
				paper.rotateElementHelper = paper.path(pathTransfGroupRotate+' z').attr({stroke:'none'});
				var newPath, trPath = Raphael.transformPath(paper.rotateElementHelper.attrs.path, "R"+ 0+' '+paper.width/2+' '+paper.height/2);
				paper.rotateElementHelper.attr({'path' : trPath});
				if(flipX&&flipY){
					newPath = Raphael.transformPath(
						paper.rotateElementHelper.attrs.path, 
						"R"+ angle+' S '+flipX+' '+flipY+' '+paper.width/2+' '+paper.height/2
					);
				}
				else {
					newPath = Raphael.transformPath(paper.rotateElementHelper.attrs.path, "R"+ angle+' '+paper.width/2+' '+paper.height/2);
				}
				
				group.forEach(function(el, indx){
					var indexInSpline = indx+1;
					var deltaX = newPath[indexInSpline][1] - paper.rotateElementHelper.attrs.path[indexInSpline][1];
					var deltaY = newPath[indexInSpline][2] - paper.rotateElementHelper.attrs.path[indexInSpline][2];
					
					//рулетки
					if(el.parentRuler || el.parentTextAnnotation){
						el.translate[0] = deltaX;
						el.translate[1] = deltaY;
						el.curvTranslate = el.translate;
						if(flipX&&flipY){
							el.curvScale = [flipX, flipY];
						}
						else{
							el.curvScale = [1,1];
							el.rotate = angle;
						}
						
						paper.applyTransformsString(el);
						
						//convert from curve to line
						var elPath = el.attrs.path;
						for(var i=0; i<elPath.length;i++){
							if(i>0){
								elPath[i][0]='L';
								elPath[i][1]=el.attrs.path[i][5];
								elPath[i][2]=el.attrs.path[i][6];
							}
						}
						el.attr({'path':elPath});
						
						if(el.parentRuler){paper.setRuler(el.parentRuler, [newPath[indexInSpline][1], newPath[indexInSpline][2]]);}
						if(el.parentTextAnnotation){paper.setTextAnnotationBlock(el.parentTextAnnotation);}
						
						return;
					}
					
					el.translate[0] += deltaX;
					el.translate[1] += deltaY;
					el.curvTranslate = el.translate;
					if(flipX&&flipY){
						el.scale = [el.scale[0]*flipX,el.scale[1]*flipY];
					}
					else{
						el.scale = [el.scale[0],el.scale[1]];
						el.rotate +=angle;
					}
					paper.applyTransforms(el);
				});

				paper.rotateElementHelper.attr({
					'path' : newPath
				});
			};
			
			//отображение узловых точек пути
			paper.showPathNodes = function(c){
				var cpath = c.attrs.path,
					nodeCount = cpath.length;
				for(var i=0; i<nodeCount; i++){
					var lineNode = paper.rect(0, 0, 10, 10);
						lineNode.attr({
							'fill':'#0B65BF',
							'stroke':'#1E5799'
						});
						paper.setProperties(lineNode);
						paper.setDraggable(lineNode);
						switch (cpath[i][0]){
							case "L":
								lineNode.translate[0] = c.matrix.x(cpath[i][1], cpath[i][2]);
								lineNode.translate[1] = c.matrix.y(cpath[i][1], cpath[i][2]);
								break;

							case "M":
								lineNode.translate[0] = c.matrix.x(cpath[i][1], cpath[i][2]);
								lineNode.translate[1] = c.matrix.y(cpath[i][1], cpath[i][2]);
								//проверка на след нод - если он с кривой то отображаем nodehandler
								paper.setSplineNode(c, lineNode, i);
								break;

							case "C":
								lineNode.translate[0] = c.matrix.x(cpath[i][5], cpath[i][6]);
								lineNode.translate[1] = c.matrix.y(cpath[i][5], cpath[i][6]);
								//если дальше еще одна курва, то сложная точка
								paper.setSplineNode(c, lineNode, i);
								break;

							case "T":
								lineNode.translate[0] = c.matrix.x(cpath[i][1], cpath[i][2]);
								lineNode.translate[1] = c.matrix.y(cpath[i][1], cpath[i][2]);
								break;

							case "Z":
								lineNode.remove();
								continue;
						}
					paper.applyTransforms(lineNode);
					paper.transformBox.push(lineNode);
				}
			};
			
			//отображать элемент(ы) управления узловой точкой пути
			paper.setSplineNode = function(parent, node, nodeIndex){
				//node - точка кривой
				//parent - кривая
				//nodeIndex - точка в parent.attrs.path
				node.click(function(){
					var linePath = parent.attrs.path;
					
					if(paper.splineHandler){paper.splineHandler.remove();}
					paper.splineHandler = paper.set();
					
					var createHandler = function(handlerX,handlerY,pointIndex, handlerIndex){
						var nodeHandler = paper.circle(handlerX, handlerY, 10)
						nodeHandler.attr({
							"fill":"#0B65BF",
							"stroke":"#BCBCBC"
						});
						paper.setProperties(nodeHandler);
						paper.setDraggable(nodeHandler);
						nodeHandler.parent = parent;
						nodeHandler.pointIndex = pointIndex;
						nodeHandler.handlerIndex = handlerIndex;
						
						var nodeHandlerLine = paper.path('M'+node.translate[0]+' '+node.translate[1]+'L'+handlerX+' '+handlerY);
						nodeHandlerLine.attr({
							"stroke":"#a7c7dc",
							"cursor":"default",
							"stroke-dasharray": "- "
						});
						paper.splineHandler.push(nodeHandler);
						paper.splineHandler.push(nodeHandlerLine);
					};
					//если впереди курва значит будет ус
					if(linePath[nodeIndex+1] && linePath[nodeIndex+1][0]=='C'){
						var newHandlerCoordsX = parent.matrix.x(linePath[nodeIndex+1][1], linePath[nodeIndex+1][2]),
							newHandlerCoordsY = parent.matrix.y(linePath[nodeIndex+1][1], linePath[nodeIndex+1][2]);
						
						createHandler(newHandlerCoordsX, newHandlerCoordsY, nodeIndex, nodeIndex+1);
					}
					switch(linePath[nodeIndex][0]){
						case 'C':
							var newHandlerCoordsX = parent.matrix.x(linePath[nodeIndex][3], linePath[nodeIndex][4]),
								newHandlerCoordsY = parent.matrix.y(linePath[nodeIndex][3], linePath[nodeIndex][4]);
								
							createHandler(newHandlerCoordsX, newHandlerCoordsY, nodeIndex, nodeIndex);
							break;
					}
				});
			};
			
			//изменение позиции направляющей пути
			paper.transformSpline = function(c, deltaX, deltaY, index){
				var splinePath = c.parent.attrs.path,
					lineToHandler = paper.splineHandler[index+1],
					oldLinePath = lineToHandler.attrs.path;
								
				if(splinePath[c.handlerIndex]!=splinePath[c.pointIndex]){
					splinePath[c.handlerIndex][1]+=deltaX;
					splinePath[c.handlerIndex][2]+=deltaY;
				}
				else{
					splinePath[c.handlerIndex][3]+=deltaX;
					splinePath[c.handlerIndex][4]+=deltaY;
				}
				oldLinePath[1][1]+=deltaX;
				oldLinePath[1][2]+=deltaY;
				lineToHandler.attr({
					'path': oldLinePath
				});
				c.parent.attr({
					'path': splinePath
				});
			};
			
			paper.saveContent = function(){
				var currentSeries = dViewer.studies[dViewer.currentSeries],
					currentFrame = currentSeries.instances[dViewer.activeFrame],
					params = {}, r = null,
					currentUser = getGlobalOptions().CurMedPersonal_id,
					exportSvg = paper.exportCompileSVGObjects(),
					checkForAttachFrameInterval = function(attachFrames){
						var curFrame = dViewer.activeFrame+1,
							arr = attachFrames.split(','),
							numb = /(\b(\d+)\b)/g,
							res = false;
							
						arr.forEach(function(val, ind){
							var match,
								num = 0;
								
							while (match = numb.exec(val))
							{
								if(num&&(num<=curFrame&&curFrame<=match[0]))
								{res = true;}
								else if(curFrame==match[0])
								{
									res = true;
								}
								num = match[0];
							}
						});
						return res;
					};
					
				params = {
					'study_uid': currentSeries.studyUID,
					'seriesUID': currentSeries.seriesUID,
					'canvasXmlData': exportSvg||'empty',
					'attachFrames': dViewer.activeFrame+1
				};
				
				if(currentFrame && currentFrame.numberOfFrames==1){
					//обычный инстанс
					params.sopIUID  = currentFrame.sopUID;
					
					//если мы перезаписываем текущую аннотацию - отправляем ид для перезаписи
					//перезаписываем только аннотацию текущего пользователя
					dViewer.annotationStore.each(function(rec, ind){
						r = rec.data;
						if (
							(
								r.DicomStudyNote_PictureUID == currentFrame.sopUID
								|| checkForAttachFrameInterval(r.DicomStudyNote_AttachFrames.toString())
							)
							&& r.MedPersonal_id == currentUser
						) {
							params.DicomStudyNote_id = r.DicomStudyNote_id;
							//хочу чтобы при перезаписи сохранялась привязка к кадрам
							params.attachFrames = r.DicomStudyNote_AttachFrames;
						}
					});
				}
				else{
					//видеопетля
					var currentLoopSeries = (dViewer.instThumbsView.getSelectionCount()>0)?(dViewer.instThumbsView.getSelectedRecords()[0].data.num):0;
					
					currentFrame = currentSeries.instances[currentLoopSeries];
					
					params.sopIUID = currentFrame.sopUID;

					//если мы перезаписываем текущую аннотацию - отправляем ид для перезаписи
					dViewer.annotationStore.each(function(rec, ind){
						r = rec.data;
						if (
							( r.DicomStudyNote_PictureUID == currentFrame.sopUID ) &&
							( r.MedPersonal_id == currentUser ) &&
							( checkForAttachFrameInterval(r.DicomStudyNote_AttachFrames.toString()) )
						) {
							params.DicomStudyNote_id = r.DicomStudyNote_id;
							//хочу чтобы при перезаписи сохранялась привязка к кадрам
							params.attachFrames = r.DicomStudyNote_AttachFrames;
						}
					});
				}
				
				if(params.canvasXmlData!='empty'){
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении аннотации возникли ошибки'));
						},
						params: params,
						success: function(response, options) {
							if ( response.responseText )
							{
								var result = Ext.util.JSON.decode(response.responseText),
									oldRec = dViewer.annotationStore.query('DicomStudyNote_id', result.DicomStudyNote_id).get(0);
								
								//удаляем старую запись
								if(oldRec)dViewer.annotationStore.remove(oldRec);
								
								var rec = new Ext.data.Record({
									'DicomStudyNote_id': result.DicomStudyNote_id,
									'DicomStudyNote_UID':params.study_uid,
									'DicomStudyNote_SeriesUID':params.seriesUID,
									'DicomStudyNote_PictureUID':params.sopIUID,
									'DicomStudyNote_AttachFrames':params.attachFrames,
									'DicomStudyNote_XmlData':result.DicomStudyNote_XmlData,
									'Person_FIO': result.Person_FIO,
									'pmUser_insID':getGlobalOptions().pmuser_id,
									'MedPersonal_id':currentUser,
									'visible': true
								});
								
								dViewer.annotationStore.add(rec);
								
								dViewer.annotationStore.each(function(rec, ind){
									if(dViewer.commitUsersStore.find('MedPersonal_id', rec.data.MedPersonal_id)==-1){
										dViewer.commitUsersStore.add(rec.copy());
									}
								});
							}
						}.createDelegate(this),
						url: '/?c=Dicom&m=saveDicomSvgAnnotation'
					});
				}
				else{
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(langs('Ошибка'), langs('При удалении аннотации возникли ошибки'));
						},
						params: params,
						success: function(response, options) {
							if ( response.responseText )
							{
								var result = Ext.util.JSON.decode(response.responseText),
									oldRec = dViewer.annotationStore.query('DicomStudyNote_id', params.DicomStudyNote_id).get(0);
								
								//удаляем старую запись
								if(oldRec)dViewer.annotationStore.remove(oldRec);
							}
						}.createDelegate(this),
						url: '/?c=Dicom&m=deleteDicomSvgAnnotation'
					});
				}
			};
			
			//функция обработки объектов raphaele для их экспорта
			paper.exportCompileSVGObjects = function(){
				var exportedObjects = [],
					skipSetsCount = 0,
					countElements = 0,
					exportedXML = '';
					
				if(paper.transformBox){paper.transformBox.remove();}
				
				//собираем объекты - в объекты, сеты - в сеты
				paper.forEach(function(e, i){
					//отслеживаем сеты
					if(skipSetsCount==0){
						if(e.parentRuler){
							//отслеживаем линейки (ruler)
							countElements = e.parentRuler.length;
							skipSetsCount = countElements;
							
							switch(e.parentRuler.ctype){
								case 'ruler': e.node.setAttribute('itype', 'ruler'); break;
								case 'rulerAngle': e.node.setAttribute('itype', 'rulerAngle'); break;
							}
						}
						if(e.parentTextAnnotation){
							//отслеживаем комменты
							countElements =  e.parentTextAnnotation.length;
							skipSetsCount = countElements;
							e.node.setAttribute('itype', 'txtannot');
							e.node.setAttribute('textcontent', e.parentTextAnnotation[2].attrs.text.replace(/\n/g,'*br'));
							e.node.setAttribute('textfill', e.parentTextAnnotation[2].attrs.fill);
							e.node.setAttribute('textsize', e.parentTextAnnotation[2].attrs['font-size']);
						}
					}

					if( skipSetsCount == 0 || skipSetsCount == countElements ){
						exportedObjects.push(e);
						exportedXML +=e.node.outerHTML;
					}
					(skipSetsCount>0)?skipSetsCount -=1:true;
				});
				
				return exportedXML;
			};
			
			paper.importCompileSVGObjects = function(rawSVG){
				try {
					if (typeof rawSVG === 'undefined')
						throw 'No data was provided.';

					rawSVG = rawSVG.replace(/\n|\r|\t/gi, '');

					var findAttr  = new RegExp('([a-z\-]+)="(.*?)"','gi'),
						findStyle = new RegExp('([a-z\-]+) ?: ?([^ ;]+)[ ;]?','gi'),
						findNodes = new RegExp('<(rect|polyline|circle|ellipse|path|polygon|image|text).*?(\/>|<\/text>)','ig'),
						convertFromCurveToLine = function(splineEl){
							var elPath = splineEl.attrs.path;
							for(var i=0; i<elPath.length;i++){
								if(i>0){
									elPath[i][0]='L';
									elPath[i][1]=splineEl.attrs.path[i][5];
									elPath[i][2]=splineEl.attrs.path[i][6];
								}
							}
							return elPath;
						};
						//findNodes = new RegExp('<(rect|polyline|circle|ellipse|path|polygon|image|text).*?\/>','gi');

					while(match = findNodes.exec(rawSVG)){
						var shape, style,
							attr = { 'fill':'#000' },
							node = RegExp.$1;

						while(findAttr.exec(match)){
							switch(RegExp.$1) {
								case 'stroke-dasharray':
									attr[RegExp.$1] = '- ';
									break;
								case 'style':
									style = RegExp.$2;
									break;
								default:
									attr[RegExp.$1] = RegExp.$2;
									break;
							}
						}

						if (typeof attr['stroke-width'] === 'undefined')
							attr['stroke-width'] = (typeof attr['stroke'] === 'undefined' ? 0 : 1);

						if (style)
							while(findStyle.exec(style))
							attr[RegExp.$1] = RegExp.$2;

						var ruler, shapeCoords, rulerTextLength;

						switch(node) {
							case 'rect':
								shape = this.rect();
								paper.setProperties(shape);
								paper.setDraggable(shape);
								paper.setClickable(shape);
								shape.transformable = true;
								shape.hasOptions = true;
								paper.setDoubleClickable(shape);
							break;
							case 'circle':
								shape = this.circle();
								paper.setProperties(shape);
								paper.setDraggable(shape);
								paper.setClickable(shape);
								shape.transformable = true;
								shape.hasOptions = true;
								paper.setDoubleClickable(shape);
							break;
							case 'ellipse':
								shape = this.ellipse();
								paper.setProperties(shape);
								paper.setDraggable(shape);
								paper.setClickable(shape);
								shape.transformable = true;
								shape.hasOptions = true;
								paper.setDoubleClickable(shape);
							break;
							case 'path':
								//сделать проверку на вход тип объекта
								//обычный path или линейка или текстовая аннотация
								shape = this.path(attr['d']);
								if(attr.itype){
									switch(attr.itype){
										case 'ruler':
											ruler = paper.set();
											shapeCoords = [shape.attrs.path[0][1],shape.attrs.path[0][2]];
											rulerTextLength = paper.text(shapeCoords[0], shapeCoords[1], "0px").attr({'fill': '#ff7f04','font-size': 14});

											paper.setProperties(shape);
											paper.setClickable(shape);
											shape.transformable = true;

											ruler.push(shape);
											ruler.push(rulerTextLength);

											ruler.ctype = 'ruler';

											shape.parentRuler = ruler;
											paper.setProperties(ruler);
											paper.setDraggable(ruler);
											paper.setClickable(ruler);

											if(!attr.transform)paper.setRuler(ruler, shapeCoords);
										break;
										case 'rulerAngle':
											ruler = paper.set();
											shapeCoords = [shape.attrs.path[0][1],shape.attrs.path[0][2]];
											rulerTextLength = paper.text(shapeCoords[0], shapeCoords[1], "0px").attr({'fill': '#ff7f04','font-size': 14});

											var angleCurve = paper.createCustomCurve(shapeCoords, 'L', false).attr({"stroke":"#91e842"}).hide(),
												angleCurvetext = paper.text(shapeCoords[0], shapeCoords[1], '0'+'°').attr({"fill":"#91e842",'font-size': 14}).hide();

											paper.setProperties(shape);
											paper.setClickable(shape);
											shape.transformable = true;

											ruler.push(shape);
											ruler.push(rulerTextLength);

											ruler.push(angleCurve);
											ruler.push(angleCurvetext);

											ruler.ctype = 'rulerAngle';

											shape.parentRuler = ruler;
											paper.setProperties(ruler);
											paper.setDraggable(ruler);
											paper.setClickable(ruler);

											if(!attr.transform)paper.setRuler(ruler, shapeCoords);
										break;
										case 'txtannot':
											shapeCoords = [shape.attrs.path[0][1],shape.attrs.path[0][2]];
											var textAnnotation = paper.set(),
												line = shape.attr({'stroke':'#fff','stroke-width': 1}),
												annotationRect = paper.rect(0,0,10,10),
												annotationText = paper.text(shapeCoords[0], shapeCoords[1], attr.textcontent.replace('*br', '\n')).attr({'fill': attr.textfill, 'font-size': attr.textsize}),
												annotationCircle = paper.circle(shapeCoords[0], shapeCoords[1],3).attr({'fill': '#fff'});

											paper.setProperties(line);
											paper.setClickable(line);
											line.transformable = true;

											paper.setDoubleClickable(annotationText);
											annotationText.hasOptions = true;

											textAnnotation.push(line);
											textAnnotation.push(annotationRect);
											textAnnotation.push(annotationText);
											textAnnotation.push(annotationCircle);

											textAnnotation.ctype = 'annotationText';
											line.parentTextAnnotation = textAnnotation;
											annotationText.parentTextAnnotation = textAnnotation;
											paper.setProperties(textAnnotation);
											paper.setDraggable(textAnnotation);
											paper.setClickable(textAnnotation);

											if(!attr.transform)paper.setTextAnnotationBlock(textAnnotation);
										break;
									}
								}
								else{
									paper.setProperties(shape);
									paper.setDraggable(shape);
									paper.setClickable(shape);
									shape.transformable = true;
									shape.hasOptions = true;
									paper.setDoubleClickable(shape);
								}
							break;
							case 'polygon':
								shape = this.polygon(attr['points']);
								paper.setProperties(shape);
								paper.setDraggable(shape);
								paper.setClickable(shape);
								shape.transformable = true;
								shape.hasOptions = true;
								paper.setDoubleClickable(shape);
							break;
							case 'text':
								shape = this.text();

								var findTextValue = new RegExp('<tspan.*?>(.*?\)<\/tspan>','ig'),
									rawtext = '',
									numspan = 0;

								while(findTextValue.exec(match[0])){
									rawtext += (numspan>0)?'\n'+RegExp.$1:''+RegExp.$1;
									numspan++;
								}
								shape.attr({'text': rawtext});
								paper.setProperties(shape);
								paper.setDraggable(shape);
								paper.setClickable(shape);
								shape.transformable = true;
								shape.hasOptions = true;
								paper.setDoubleClickable(shape);
							break;
							case 'image':
							  shape = this.image();
							break;
						}

						shape.attr(attr);

						//применение трансформаций
						if(attr.transform){
							var tr = attr.transform.replace(/matrix\((.*)\)/,'$1').split(','),
								matrx = Raphael.matrix(tr[0],tr[1],tr[2],tr[3],tr[4],tr[5]),
								matrixParams = matrx.split(),
								newbbox = shape.getBBox(false),
								centerBbox = {'x':newbbox.x+newbbox.width/2, 'y':newbbox.y+newbbox.height/2},
								x = matrx.x(centerBbox.x, centerBbox.y),
								y = matrx.y(centerBbox.x, centerBbox.y);

							//если сложная фигура
							if(attr.itype){
								switch(attr.itype){
									case 'txtannot':
										textAnnotation[0].curvTranslate = [x-centerBbox.x,y-centerBbox.y];
										textAnnotation[0].curvScale = [matrixParams.scalex,matrixParams.scaley];
										textAnnotation[0].rotate = matrixParams.rotate;

										paper.applyTransformsString(textAnnotation[0]);

										textAnnotation[0].attr({'path':convertFromCurveToLine(textAnnotation[0])});

										paper.setTextAnnotationBlock(textAnnotation);
									break;
									case 'ruler':
										ruler[0].curvTranslate = [x-centerBbox.x,y-centerBbox.y];
										ruler[0].curvScale = [matrixParams.scalex,matrixParams.scaley];
										ruler[0].rotate = matrixParams.rotate;

										paper.applyTransformsString(ruler[0]);
										ruler[0].attr({'path':convertFromCurveToLine(ruler[0])});

										paper.setRuler(ruler, [ruler[0].attrs.path[0][1],ruler[0].attrs.path[0][2]]);
									break;
									case 'rulerAngle':
										ruler[0].curvTranslate = [x-centerBbox.x,y-centerBbox.y];
										ruler[0].curvScale = [matrixParams.scalex,matrixParams.scaley];
										ruler[0].rotate = matrixParams.rotate;

										paper.applyTransformsString(ruler[0]);
										ruler[0].attr({'path':convertFromCurveToLine(ruler[0])});

										paper.setRuler(ruler, [ruler[0].attrs.path[0][1],ruler[0].attrs.path[0][2]]);
									break;
								}
							}
							//ежели обычная
							else{
								shape.translate = [x-centerBbox.x,y-centerBbox.y];
								shape.scale = [matrixParams.scalex,matrixParams.scaley];
								shape.rotate = matrixParams.rotate;

								paper.applyTransforms(shape);
							}
						}

						if (typeof set !== 'undefined')
							set.push(shape);
					}
				}
				catch (error) {
					alert('Ошибка при загрузке аннотаций (' + error + ')');
				}
			};
			
			//загрузка аннотаций
			paper.loadContent = function(){
				//console.log('load content у нас есть плюшечки');
				//собираем уникальных пользователей
				dViewer.commitUsersStore.removeAll();
				dViewer.annotationStore.each(function(rec, ind){
					//по умолчанию отображаем только текущего пользователя
					rec.set('visible', (getGlobalOptions().CurMedPersonal_id==rec.data.MedPersonal_id));
					
					if(dViewer.commitUsersStore.find('MedPersonal_id', rec.data.MedPersonal_id)==-1){
						dViewer.commitUsersStore.add(rec.copy());
					}
				});
				
				var xmlData =  dViewer.getCurrentFrameAnnotaton();
				
				paper.clear();
				if(xmlData){
					paper.importCompileSVGObjects(xmlData);
				}
			};
			
			//если первый запуск
			if( dViewer.annotationStore.getCount() > 0){
				paper.loadContent()
			}
			else{
				paper.clear();
			}
		}
		return paper;
	},
	
	resetCanvas: function(){
		var dViewer = this,
			canv = this.getCanvas(),
			canvContext = canv.getContext("2d"),
			frameImg = dViewer.series[dViewer.activeFrame];

		canvContext.save();
		canvContext.setTransform(1,0,0,1,0,0);
		canvContext.fillRect(0, 0, canv.width, canv.height);
		canvContext.restore();
	},
	
	//по сути - рендеринг кадра
	setActiveFrame: function(numFrame){
		var dViewer = this,
			ecgCanvasContainer = Ext.get('dicomViewer-ecgCanvasContainer'),
			videoLoopedSeries = dViewer.studies[dViewer.currentSeries].videoLoop;

		(numFrame<0)?(numFrame = 0):false;
		(numFrame>dViewer.series.length-1)?(numFrame = dViewer.series.length-1):false;
		dViewer.activeFrame = numFrame||0;
		
		var frameImg = dViewer.series[dViewer.activeFrame],
			canvasEl = dViewer.getCanvas(),
			ctx = canvasEl.getContext('2d'),
			calcH = Math.round(canvasEl.height - frameImg.height)/2,
			calcW = Math.round(canvasEl.width - frameImg.width)/2,
			deltaHeight = canvasEl.width/frameImg.width;
			
		dViewer.currentFrameLabel.setText((dViewer.activeFrame+1)+langs(' из ')+dViewer.countPictures);
		
		ctx.fillRect(0, 0, canvasEl.width, canvasEl.height);
		if(ecgCanvasContainer) ecgCanvasContainer.child('svg').dom.innerHTML = '';
		//если экг то фигачим свг иначе рисуем пикчу
		if(frameImg.svg && ecgCanvasContainer){
			var s = $(frameImg.svg).find('#scaling')[0];
					
			ecgCanvasContainer.child('svg').dom.appendChild(s);
		}
		else{
			ctx.drawImage(frameImg, calcW, calcH);
		}

		dViewer.slider.setValue(dViewer.activeFrame);
		if(videoLoopedSeries!=true){
			dViewer.instThumbsView.selected.removeClass(dViewer.instThumbsView.selectedClass);
			dViewer.instThumbsView.selected.elements = [];
			dViewer.instThumbsView.select(dViewer.activeFrame);
		}
		//svg
		if(dViewer.paper){
			dViewer.paper.clear();
			var xmlData =  dViewer.getCurrentFrameAnnotaton();
			if(xmlData)	dViewer.paper.importCompileSVGObjects(xmlData);
		}
	},
	
	setSlider: function(num, showVideoCntEls){
		var dViewer = this,
			elem = dViewer.getEl(),
			slider = dViewer.slider,
			marginStyle = '',
			currentSer = dViewer.studies[dViewer.currentSeries];
		
		if(currentSer){
			if(num>1){
				if(currentSer.videoLoop||showVideoCntEls){
					dViewer.displayVideoControlEls(true);
					slider.setWidth(dViewer.getInnerWidth()-130);
					marginStyle = 'margin: 0px;';
				}
				else{
					slider.setWidth(dViewer.getInnerWidth()-40);
					marginStyle = 'margin: 0 0 0 20px;';
					dViewer.displayVideoControlEls(false)
				}
				slider.setVisible(true);

			}
			else{
				slider.setVisible(false);
				dViewer.displayVideoControlEls(false)
			}
			
		}
		//не всегда apply срабатывает(
		if(slider.el){
			slider.el.dom.style = marginStyle;
			marginStyle = 'margin: 0px;';
		}
		
		Ext.apply(slider, {
			style: marginStyle,
			maxValue: num-1
		});
	},
	
	//отображение/скрытие элементов управления видео петлями
	displayVideoControlEls: function(visibility){
		var dViewer = this,
			cont = dViewer.getComponent('dicom-slidercontainer');
			
		if(cont){
			if(visibility){
				cont.items.get(1).show();
				cont.items.get(3).show();
			}
			else{
				cont.items.get(1).hide();
				cont.items.get(3).hide();
			}
		}
	},
	
	getCurrentFrameAnnotaton: function(num){
		var dViewer = this,
			xmlData = null, 
			currentAnnotation,
			currentSeries = dViewer.studies[dViewer.currentSeries],
			currentFrame = currentSeries.instances[dViewer.activeFrame],
			currentUser = getGlobalOptions().CurMedPersonal_id,
			checkForAttachFrameInterval = function(attachFrames){
				var curFrame = dViewer.activeFrame+1,
					arr = attachFrames.split(','),
					numb = /(\b(\d+)\b)/g,
					res = false;
					
				arr.forEach(function(val, ind){
					var match,
						num = 0;
						
					while (match = numb.exec(val))
					{
						if(num&&(num<=curFrame&&curFrame<=match[0]))
						{res = true;}
						else if(curFrame==match[0])
						{
							res = true;
						}
						num = match[0];
					}
				});
				return res;
			};
		
		if(	dViewer.vMode == 'videoPlay'){
			dViewer.annotationStore.each(function(rec, ind){
				var r = rec.data;
				
				if (
					( r.DicomStudyNote_PictureUID == currentFrame.sopUID ) &&
					( r.visible == true )
				) {
					dViewer.paper.canvas.innerHTML += r.DicomStudyNote_XmlData;
				}
			});
		}
		else{
			if(currentFrame && currentFrame.numberOfFrames==1){
				//обычный инстанс
				dViewer.annotationStore.each(function(rec, ind){
					var r = rec.data;
					
					if (
						(
							r.DicomStudyNote_PictureUID == currentFrame.sopUID ||
							checkForAttachFrameInterval(r.DicomStudyNote_AttachFrames.toString())
						) &&
						//( r.MedPersonal_id == currentUser )  &&
						r.visible == true
					) {
						//каждый может редактировать только свои аннотации
						if( r.MedPersonal_id == currentUser ) xmlData += r.DicomStudyNote_XmlData;
						else { dViewer.paper.canvas.innerHTML += r.DicomStudyNote_XmlData; }
					}
				});
			}
			else{
				//видеопетля
				var currentLoopSeries = (dViewer.instThumbsView.getSelectionCount()>0)?(dViewer.instThumbsView.getSelectedRecords()[0].data.num):0;
					
				currentFrame = currentSeries.instances[currentLoopSeries];
				
				dViewer.annotationStore.each(function(rec, ind){
					var r = rec.data;
					
					if (
						(r.DicomStudyNote_PictureUID == currentFrame.sopUID) &&
						//(r.MedPersonal_id == currentUser) &&
						(checkForAttachFrameInterval(r.DicomStudyNote_AttachFrames.toString())) &&
						( r.visible == true )
					) {
						//каждый может редактировать только свои аннотации
						if( r.MedPersonal_id == currentUser ) xmlData += r.DicomStudyNote_XmlData;
						else { dViewer.paper.canvas.innerHTML += r.DicomStudyNote_XmlData; }
					}
				})
			}
			
			if(xmlData){return(xmlData);}
			else {return false;}
		}
	},
	
	//настройка курсора над канвой
	setCursorOnCanvas: function(cursor){
		var el = document.getElementById('dicomViewer-canvasAnnotationEl');
		if(el) el.style.cursor = cursor;
	},
	
	//tools dviewer
	startDragFuntion: function(){
		var dViewer = this;
		dViewer.mouseDrugInfo.canvasOldPos = null;
		dViewer.mouseDrugInfo.canvasDelta = null;
	},
	endDragFuntion: function(){
		var dViewer = this,
			dragAction = dViewer.mode,
			magnifyExtEl = Ext.get("dicomViewer-canvasZoomEl"),
			buttons = dViewer.topCommentsToolbar.items,
			lineToolBtn = buttons.itemAt(buttons.findIndex('ctCls', 'lineToolBtn')),
			rectangleToolBtn = buttons.itemAt(buttons.findIndex('ctCls', 'rectangleToolBtn')),
			ellipseToolBtn = buttons.itemAt(buttons.findIndex('ctCls', 'ellipseToolBtn')),
			freehandSplineBtn = buttons.itemAt(buttons.findIndex('ctCls', 'freehandSplineBtn')),
			createRulerButt = buttons.itemAt(buttons.findIndex('ctCls', 'rulerToolBtn')),
			createRulerAngleButt = buttons.itemAt(buttons.findIndex('ctCls', 'rulerAngleToolBtn')),
			createTextAnnotationButt = buttons.itemAt(buttons.findIndex('ctCls', 'textAnnotationToolBtn')),
			drawObject = null;
			
		switch(dragAction){
			case 'magnifyAction':
				magnifyExtEl.setVisible(false, true);
				break;

			case 'rectangleAction':
				drawObject = dViewer.paper.createElement;
				dViewer.paper.setProperties(drawObject);
				dViewer.paper.setDraggable(drawObject);
				dViewer.paper.setClickable(drawObject);
				drawObject.transformable = true;
				rectangleToolBtn.toggle(false);
				drawObject.hasOptions = true;
				dViewer.paper.setDoubleClickable(drawObject);
				dViewer.mode = 'default';
				dViewer.paper.createElement = null;
				break;

			case 'ellipseAction':
				drawObject = dViewer.paper.createElement;
				dViewer.paper.setProperties(drawObject);
				dViewer.paper.setDraggable(drawObject);
				dViewer.paper.setClickable(drawObject);
				ellipseToolBtn.toggle(false);
				drawObject.transformable = true;
				drawObject.hasOptions = true;
				dViewer.paper.setDoubleClickable(drawObject);
				dViewer.mode = 'default';
				dViewer.paper.createElement = null;
				break;

			case 'lineAction':
				drawObject = dViewer.paper.createElement;
				dViewer.paper.setProperties(drawObject);
				dViewer.paper.setDraggable(drawObject);
				dViewer.paper.setClickable(drawObject);
				lineToolBtn.toggle(false);
				drawObject.transformable = true;
				drawObject.hasOptions = true;
				dViewer.paper.setDoubleClickable(drawObject);
				dViewer.mode = 'default';
				dViewer.paper.createElement = null;
				break;

			case 'createFreehandSpline':
				freehandSplineBtn.toggle(false);
				dViewer.mode = 'default';
				dViewer.paper.createElement = null;
				break;

			case 'createRuler':
				createRulerButt.toggle(false);
				dViewer.mode = 'default';
				dViewer.paper.createElement = null;
				break;

			case 'createRulerAngle':
				//ограничение для угловой линейки на 3 точки
				if(dViewer.paper.createElement[0].attrs.path.length==3){
					dViewer.mode = 'default';
					dViewer.paper.createElement = null;
					dViewer.setCursorOnCanvas('default');
					createRulerAngleButt.toggle(false);
				}
				break;

			case 'createCustomFigure':
				dViewer.mode = 'default';
				dViewer.paper.createElement = null;
				break;

			case 'createTextAnnotation':
				var tAn = dViewer.paper.createElement,
					lineA = tAn[0],
					textA = tAn[1],
					textWrapper = tAn[2];
				
				dViewer.paper.setTextAnnotationBlock(tAn);
				textA.show();
				textWrapper.show();
				dViewer.optionsWindows.textOptsWin.show();
				dViewer.optionsWindows.textOptsWin.textArea.setValue(textA.attrs.text);
				break;
		}
		if(drawObject){dViewer.paper.showTransformBox(drawObject);}
	},
	dragFuntion: function(e){
		var dViewer = this,
			dragAction = dViewer.mode,
			canvasExtEl = Ext.get("dicomViewer-canvas"),	
			canvasEl = dViewer.getCanvas(),
			canvContext = canvasEl.getContext("2d"),
			frameImg = dViewer.series[dViewer.activeFrame],
			oldPos = dViewer.mouseDrugInfo.canvasOldPos,
			canvasDelta = dViewer.mouseDrugInfo.canvasDelta,
			magnifyExtEl = Ext.get("dicomViewer-canvasZoomEl"),
			magnifyCanvasEl = dViewer.getCanvasZoomEl(),
			ctxMagnifyCanvas = magnifyCanvasEl.getContext("2d"),
			localCoords = [e.xy[0]-canvasExtEl.getXY()[0], e.xy[1]-canvasExtEl.getXY()[1]];
		
		//поправка по трансформации
		if(canvContext.oldTransformation){
			var ot = canvContext.oldTransformation;
				localCoords = [
					(e.xy[0]-canvasExtEl.getXY()[0]-ot[2])/ot[0],
					(e.xy[1]-canvasExtEl.getXY()[1]-ot[3])/ot[1]
				];
		}
		
		//данные для манипуляций
		if(!oldPos){dViewer.mouseDrugInfo.canvasOldPos = e.xy;}
		else{
			canvasDelta = [e.xy[0] - oldPos[0], e.xy[1] - oldPos[1]];
			
			//правка дельты по текущим трансформам
			if(canvContext.oldTransformation){
				canvasDelta[0] /=canvContext.oldTransformation[0];
				canvasDelta[1] /=canvContext.oldTransformation[1];
			}
			
			dViewer.mouseDrugInfo.canvasOldPos = e.xy;
		}
		//экшены, которые требуют перетаскивания
		if(canvasDelta){
			switch(dragAction){
				case 'panAction':
					dViewer.resetCanvas();
					if(canvContext.oldTransformation){
						var o = canvContext.oldTransformation,
							trans = [o[0], o[1], o[2]+canvasDelta[0],o[3]+canvasDelta[1]];
						
						//scalex scaley x y
						canvContext.setTransform(trans[0],0,0,trans[1],trans[2],trans[3]);
						canvContext.oldTransformation = [trans[0],trans[1], trans[2],trans[3]];
						if(dViewer.paper){
							//x y
							dViewer.paper.oldViewBox[0] = -trans[2]/trans[0];
							dViewer.paper.oldViewBox[1] = -trans[3]/trans[0];
						}
					}
					else {
						canvContext.transform(1,0,0,1,canvasDelta[0],canvasDelta[1]);
						canvContext.oldTransformation = [1, 1, canvasDelta[0], canvasDelta[1]];
					}
					if(canvContext.rotAngle){
						canvContext.translate(canvasEl.width/2, canvasEl.height/2);				
						canvContext.rotate(canvContext.rotAngle); 
						canvContext.translate(-canvasEl.width/2, -canvasEl.height/2);
					}
					dViewer.paper.setViewBox(dViewer.paper.oldViewBox[0],dViewer.paper.oldViewBox[1], dViewer.paper.oldViewBox[2], dViewer.paper.oldViewBox[3], false);
					dViewer.setActiveFrame(dViewer.activeFrame);
					break;

				case 'zoomAction':
					dViewer.resetCanvas();
					//соотношение тика к начальной высоте
					var canvasDeltaX = canvasDelta[0]/canvasEl.width,
						canvasDeltaY = canvasDelta[1]/canvasEl.height;
					
					if(canvContext.oldTransformation){
						var o = canvContext.oldTransformation,
							trans = [o[0]+canvasDeltaY, o[1]+canvasDeltaY, o[2]-canvasDelta[1]/2, o[3]-canvasDelta[1]/2];
						
						//scalex scaley x y
						canvContext.setTransform(trans[1],0,0,trans[1],trans[2],trans[3]);
						canvContext.oldTransformation = [trans[0],trans[1], trans[2],trans[3]];

						if(dViewer.paper){
							//x y
							dViewer.paper.oldViewBox[0] = -trans[2]/trans[0];
							dViewer.paper.oldViewBox[1] = -trans[3]/trans[0];
							//width height
							dViewer.paper.oldViewBox[2] = canvasEl.width/trans[0];
							dViewer.paper.oldViewBox[3] = canvasEl.height/trans[0];
							
							dViewer.paper.setViewBox(dViewer.paper.oldViewBox[0], dViewer.paper.oldViewBox[1], dViewer.paper.oldViewBox[2], dViewer.paper.oldViewBox[3], false);
						}
					}
					else{
						canvContext.transform(1+canvasDeltaY, 0, 0, 1+canvasDeltaY, -canvasDelta[1]/2, -canvasDelta[1]/2);
						canvContext.oldTransformation = [1+canvasDeltaX, 1+canvasDeltaY, -canvasDelta[1]/2, -canvasDelta[1]/2];
					}
					if(canvContext.rotAngle){
						canvContext.translate(canvasEl.width/2, canvasEl.height/2);				
						canvContext.rotate(canvContext.rotAngle); 
						canvContext.translate(-canvasEl.width/2, -canvasEl.height/2);
					}
					dViewer.setActiveFrame(dViewer.activeFrame);
					break;

				case 'magnifyAction':
					var customlocalCoords = [e.xy[0]-canvasExtEl.getXY()[0], e.xy[1]-canvasExtEl.getXY()[1]];
					if(!magnifyExtEl.isVisible()){magnifyExtEl.setVisible(true);}
					if( (customlocalCoords[0]-100)>0 && (customlocalCoords[1]-100)>0){
						ctxMagnifyCanvas.drawImage(canvasEl, customlocalCoords[0]-100, customlocalCoords[1]-100, 200, 200, 0,0, 300, 300);
						magnifyExtEl.setXY([e.xy[0]-100, e.xy[1]-100]);
					}
					break;

				case 'rotateAction':
					dViewer.resetCanvas();
					dViewer.rotateDegTool(canvasDelta[1]);
					break;

				case 'pencilAction':
					var newLocalCoords = [e.xy[0]-canvasExtEl.getXY()[0], e.xy[1]-canvasExtEl.getXY()[1]],
						oldLocalCoords = [oldPos[0]-canvasExtEl.getXY()[0], oldPos[1]-canvasExtEl.getXY()[1]];
					break;

				case 'rectangleAction':
					var rect = dViewer.paper.createElement;
					rect.attr({
						'width': rect.attr('width')+canvasDelta[0],
						'height': rect.attr('height')+canvasDelta[1]
					});
					break;

				case 'ellipseAction':
					var ellipse = dViewer.paper.createElement;
					ellipse.attr({
						'rx': ellipse.attr('rx')+canvasDelta[0],
						'ry': ellipse.attr('ry')+canvasDelta[1]
					});
					break;

				case 'lineAction':
					var line = dViewer.paper.createElement,
						nodeCoords = line.attrs.path,
						nodeCoordsX = localCoords[0],
						nodeCoordsY = localCoords[1];
						
					nodeCoords[1] = [nodeCoords[1][0], nodeCoordsX, nodeCoordsY];
					
					line.attr({
						'path': nodeCoords
					});
					break;

				case 'createFreehandSpline':
					var c = dViewer.paper.createCustomCurve(localCoords, 'L', false),
						trPath = c.attrs.path,
						countPoints = trPath.length;
					
					dViewer.paper.createElement.attr({
						'path': trPath
					});
					break;

				case 'createRuler':
					var ruler = dViewer.paper.createElement,
						trPath = ruler[0].attrs.path,
						countPoints = trPath.length;
					
					trPath[countPoints-1] = [trPath[countPoints-1][0], localCoords[0], localCoords[1]];

					ruler[0].attr({
						'path': trPath
					});
					
					dViewer.paper.setRuler(ruler, localCoords);
					break;

				case 'createRulerAngle':
					var ruler = dViewer.paper.createElement,
						trPath = ruler[0].attrs.path,
						countPoints = trPath.length;
					
					trPath[countPoints-1] = [trPath[countPoints-1][0], localCoords[0], localCoords[1]];

					ruler[0].attr({
						'path': trPath
					});
					
					dViewer.paper.setRuler(ruler, localCoords);
					break;

				case 'createCustomFigure':
					var figure = dViewer.paper.createElement,
						fBbox = figure.getBBox(true),
						xscaleDelta = figure.osx+canvasDelta[0]/fBbox.width,
						yscaleDelta = figure.osy+canvasDelta[1]/fBbox.height;
					
					figure.curvScale = [1+canvasDelta[0]/fBbox.width, 1+canvasDelta[1]/fBbox.height];
					figure.curvTranslate = [canvasDelta[0]/2,canvasDelta[1]/2];
					dViewer.paper.applyTransformsString(figure);
					break;

				case 'createTextAnnotation':
					var annot = dViewer.paper.createElement,
						trPath = annot[0].attrs.path,
						countPoints = trPath.length;
					
					trPath[countPoints-1] = [trPath[countPoints-1][0], localCoords[0], localCoords[1]];

					annot[0].attr({
						'path': trPath
					});
					dViewer.paper.showTransformBox(annot[0]);
					break;
			}
			
			if ( dragAction=='createCurve' 
				 || dragAction=='createClosedCurve'
				 || dragAction=='createPoliline'
				 || dragAction=='createPoligon'
			){
				var c = dViewer.paper.createElement,
					trPath = c.attrs.path,
					countPoints = trPath.length;

				if((countPoints==2)||(dragAction=='createPoliline' || dragAction=='createPoligon'))
				{trPath[countPoints-1] = [trPath[countPoints-1][0], localCoords[0], localCoords[1]];}
				else{
					trPath[countPoints-1][5] = localCoords[0];
					trPath[countPoints-1][6] = localCoords[1];
				}
				dViewer.paper.createElement.attr({
					'path': trPath
				});
				dViewer.paper.showTransformBox(c);
			}
		}
		else{
			//start drag
			switch(dViewer.mode){
				case 'rectangleAction':
					var c = dViewer.paper.rect(localCoords[0], localCoords[1], 1, 1);
					c.attr({
						"fill":"#dfed48",
						"cursor":"default"
					});
					c.enableTransform = true;
					dViewer.paper.createElement = c;
					break;

				case 'ellipseAction':
					var c = dViewer.paper.ellipse(localCoords[0], localCoords[1], 1, 1);
					c.attr({
						"fill":"#a7c7dc",
						"cursor":"default"
					});
					c.enableTransform = true;
					dViewer.paper.createElement = c;
					break;

				case 'lineAction':
					var c =  dViewer.paper.path('M'+localCoords[0]+' '+localCoords[1]+'L'+localCoords[0]+' '+localCoords[1]);
					c.attr({
						"stroke":"#a7c7dc",
						"cursor":"default",
						"stroke-width": 3
					});
					c.enableTransform = true;
					dViewer.paper.createElement = c;
					break;
			}
		}
	},
	
	imageProcess: function (func){
		var dViewer = this,
			canvasEl = dViewer.getCanvas(),
			ctx = canvasEl.getContext("2d");
			
		var pix = ctx.getImageData(0,0,canvasEl.width,canvasEl.height);
		
		//Loop through the pixels
		for(var x = 0; x < canvasEl.width; x++){
			for(var y = 0; y < canvasEl.height; y++){
				var i = (y*canvasEl.width+x)*4;
				var r = pix.data[i], g = pix.data[i+1], b = pix.data[i+2], a = pix.data[i+3];
				var ret = func(r,g,b,a,x,y);
				pix.data[i] = ret[0];
				pix.data[i+1] = ret[1];
				pix.data[i+2] = ret[2];
				pix.data[i+3] = ret[3];
			}
		}
		
		// Put the image back to the canvas
		ctx.clearRect(0,0,canvasEl.width,canvasEl.height);
		ctx.putImageData(pix,0,0);
	},
	
	panTool: function(s){
		var dViewer = this;
		
		if(s){dViewer.mode = 'panAction'; dViewer.setCursorOnCanvas('all-scroll');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	
	zoomTool: function(s){
		var dViewer = this;
		
		if(s){dViewer.mode = 'zoomAction'; dViewer.setCursorOnCanvas('ns-resize');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	
	maginfyTool: function(s){
		var dViewer = this;
		
		if(s){dViewer.mode = 'magnifyAction';}
		else{dViewer.mode = 'default';}
	},
	
	brightTool: function(t,s){
		var dViewer = this;

		if(s){
			dViewer.optionsWindows.brightnessWin.show();
		}
	},
	
	rotateCustomTool: function(t,s){
		var dViewer = this;
		
		if(s){dViewer.mode = 'rotateAction'; dViewer.setCursorOnCanvas('ns-resize');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	
	rotateDegTool: function(r){
		//эта часть для канвы
		var dViewer = this,
			canvasEl = dViewer.getCanvas(),
			canvContext = canvasEl.getContext("2d"),
			canvasWidth = canvasEl.width,
			canvasHeight = canvasEl.height,
			angle = -r;
		
		dViewer.resetCanvas();
		canvContext.translate(canvasWidth/2, canvasHeight/2);
		if(canvContext.rotAngle){ canvContext.rotAngle+= angle*Math.PI / 180; }
		else { canvContext.rotAngle = angle*Math.PI/180; }
		canvContext.rotate(angle*Math.PI / 180);
		canvContext.translate(-canvasWidth/2, -canvasHeight/2);
		dViewer.setActiveFrame(dViewer.activeFrame);
		
		//эта часть для svg
		dViewer.paper.rotateAndFlipCanvasSVG(angle);
	},
	
	flipTool: function(x,y){
		var dViewer = this,
			canvasEl = dViewer.getCanvas(),
			canvContext = canvasEl.getContext("2d");
			
		canvContext.transform(x, 0, 0, y, (x==-1)?canvasEl.width:0, (y==-1)?canvasEl.height:0);
		dViewer.setActiveFrame(dViewer.activeFrame);

		if(canvContext.rotAngle){
			dViewer.paper.rotateAndFlipCanvasSVG(canvContext.rotAngle* 180/Math.PI, x,y); 
		}
		else { dViewer.paper.rotateAndFlipCanvasSVG(0,x,y); }
	},
	
	fitTool: function(){
		var dViewer = this,
			frameImg = dViewer.series[dViewer.activeFrame],
			canvasEl = dViewer.getCanvas(),
			ctx = canvasEl.getContext('2d'),
			angle = ctx.rotAngle*180/Math.PI,
			scaleDelta = [canvasEl.width/frameImg.width, canvasEl.width/frameImg.width],
			moveDelta = [Math.abs(canvasEl.width-frameImg.width), Math.abs(canvasEl.height-frameImg.height)];

		if(frameImg.width>canvasEl.width){
			ctx.oldTransformation = [scaleDelta[0],scaleDelta[0],scaleDelta[0]*moveDelta[0]/2, scaleDelta[0]*moveDelta[1]/2];
			ctx.setTransform(ctx.oldTransformation[0],0,0,ctx.oldTransformation[1],ctx.oldTransformation[2], ctx.oldTransformation[3]);
		}
		else{
			ctx.oldTransformation = [scaleDelta[0],scaleDelta[0],-scaleDelta[0]*moveDelta[0]/2, -scaleDelta[0]*moveDelta[1]/2];
			ctx.setTransform(ctx.oldTransformation[0],0,0,ctx.oldTransformation[1],ctx.oldTransformation[2], ctx.oldTransformation[3]);
		}
		
		ctx.rotAngle = 0;
		dViewer.paper.rotateAndFlipCanvasSVG(-angle);

		dViewer.drawImageModeFit = 'fit';
		dViewer.setActiveFrame(dViewer.activeFrame);
		if(dViewer.paper){
			var delta = [frameImg.width, canvasEl.height*(frameImg.height/canvasEl.width)];
			
			dViewer.paper.oldViewBox[0] = (canvasEl.width-frameImg.width)/2;
			dViewer.paper.oldViewBox[1] = (canvasEl.height-frameImg.height)/2;
			dViewer.paper.oldViewBox[2] = delta[0];
			dViewer.paper.oldViewBox[3] = delta[1];

			dViewer.paper.setViewBox(dViewer.paper.oldViewBox[0], dViewer.paper.oldViewBox[1],dViewer.paper.oldViewBox[2],dViewer.paper.oldViewBox[3]);
		}
	},
	
	oneByOneTool: function(){
		var dViewer = this,
			canvasEl = dViewer.getCanvas(),
			ctx = canvasEl.getContext('2d'),
			angle = ctx.rotAngle*180/Math.PI;
		
		ctx.setTransform(1,0,0,1,0,0);
		ctx.oldTransformation = [1,1,0,0];
		ctx.rotAngle = 0;
		dViewer.drawImageModeFit = 'oneByOne';
		dViewer.setActiveFrame(dViewer.activeFrame);
		if(dViewer.paper){
			dViewer.paper.rotateAndFlipCanvasSVG(-angle);
			dViewer.paper.oldViewBox = [0,0,dViewer.getInnerWidth(),dViewer.getInnerHeight()];
			dViewer.paper.setViewBox(0,0,dViewer.getInnerWidth(),dViewer.getInnerHeight());
		}
	},
	
	videoPlay: function(vPlay){
		var dViewer = this,
			fpsVal = 1000/dViewer.fpsField.getValue(),
			sliderContainer = Ext.getCmp('dicom-slidercontainer').el,
			playBtnNode = sliderContainer.child('div.dicom-videobutton');

		if(vPlay){
			dViewer.vMode = 'videoPlay';
			dViewer.playPauseButton.toggle(true);
			playBtnNode.replaceClass('dicom-play', 'dicom-pause');
			dViewer.videoFrameIntervalSet = setInterval(function(){
				var currentFrame = dViewer.activeFrame,
					showFrame = dViewer.activeFrame+1,
					countFrames = dViewer.series.length-1;
					
				if(showFrame>countFrames){ showFrame = 0 }
				dViewer.setActiveFrame(showFrame);
			}, fpsVal);
		}
		else{
			dViewer.vMode = 'static';
			dViewer.playPauseButton.toggle(false);
			(playBtnNode)?playBtnNode.replaceClass('dicom-pause', 'dicom-play'):false;
			clearInterval(dViewer.videoFrameIntervalSet);
			if(dViewer.paper){
				var xmlData =  dViewer.getCurrentFrameAnnotaton();
				dViewer.paper.clear();
				if(xmlData)dViewer.paper.importCompileSVGObjects(xmlData);
			}
		}
	},
	
	//comment tools
	rectangleTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'rectangleAction'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	ellipseTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'ellipseAction'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	lineTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'lineAction'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	polilineTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'createPoliline'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	poligonTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'createPoligon'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	curveTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'createCurve'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	closedCurveTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'createClosedCurve'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	freehandSplineTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'createFreehandSpline'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	textTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'createText'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	rulerTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'createRuler'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	rulerAngleTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'createRulerAngle'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	},
	createCustomFigure: function(pathName){
		var dViewer = this,
			figure;
		
		dViewer.mode = 'createCustomFigure'; 
		switch (pathName){
			case 'arrow':
				figure = dViewer.paper.path('M21.786,12.876l7.556-4.363l-7.556-4.363v2.598H2.813v3.5h18.973V12.876z');
				break;

			case 'star':
				figure = dViewer.paper.path('M16,22.375L7.116,28.83l3.396-10.438l-8.883-6.458l10.979,0.002L16.002,1.5l3.391,10.434h10.981l-8.886,6.457l3.396,10.439L16,22.375L16,22.375z');
				break;

			case 'cross':
				figure = dViewer.paper.path('M24.778,21.419 19.276,15.917 24.777,10.415 21.949,7.585 16.447,13.087 10.945,7.585 8.117,10.415 13.618,15.917 8.116,21.419 10.946,24.248 16.447,18.746 21.948,24.248z');
				break;

			case 'check':
				figure = dViewer.paper.path('M2.379,14.729 5.208,11.899 12.958,19.648 25.877,6.733 28.707,9.561 12.958,25.308z');
				break;

			case 'attention':
				figure = dViewer.paper.path('M29.225,23.567l-3.778-6.542c-1.139-1.972-3.002-5.2-4.141-7.172l-3.778-6.542c-1.14-1.973-3.003-1.973-4.142,0L9.609,9.853c-1.139,1.972-3.003,5.201-4.142,7.172L1.69,23.567c-1.139,1.974-0.207,3.587,2.071,3.587h23.391C29.432,27.154,30.363,25.541,29.225,23.567zM16.536,24.58h-2.241v-2.151h2.241V24.58zM16.428,20.844h-2.023l-0.201-9.204h2.407L16.428,20.844z');
				break;

			case 'gradline':
				figure = dViewer.paper.path('M6.63,21.796l-5.122,5.121h25.743V1.175L6.63,21.796zM18.702,10.48c0.186-0.183,0.48-0.183,0.664,0l1.16,1.159c0.184,0.183,0.186,0.48,0.002,0.663c-0.092,0.091-0.213,0.137-0.332,0.137c-0.121,0-0.24-0.046-0.33-0.137l-1.164-1.159C18.519,10.96,18.519,10.664,18.702,10.48zM17.101,12.084c0.184-0.183,0.48-0.183,0.662,0l2.156,2.154c0.184,0.183,0.184,0.48,0.002,0.661c-0.092,0.092-0.213,0.139-0.334,0.139s-0.24-0.046-0.33-0.137l-2.156-2.154C16.917,12.564,16.917,12.267,17.101,12.084zM15.497,13.685c0.184-0.183,0.48-0.183,0.664,0l1.16,1.161c0.184,0.183,0.182,0.48-0.002,0.663c-0.092,0.092-0.211,0.138-0.33,0.138c-0.121,0-0.24-0.046-0.332-0.138l-1.16-1.16C15.314,14.166,15.314,13.868,15.497,13.685zM13.896,15.288c0.184-0.183,0.48-0.181,0.664,0.002l1.158,1.159c0.183,0.184,0.183,0.48,0,0.663c-0.092,0.092-0.212,0.138-0.332,0.138c-0.119,0-0.24-0.046-0.332-0.138l-1.158-1.161C13.713,15.767,13.713,15.471,13.896,15.288zM12.293,16.892c0.183-0.184,0.479-0.184,0.663,0l2.154,2.153c0.184,0.184,0.184,0.481,0,0.665c-0.092,0.092-0.211,0.138-0.33,0.138c-0.121,0-0.242-0.046-0.334-0.138l-2.153-2.155C12.11,17.371,12.11,17.075,12.293,16.892zM10.302,24.515c-0.091,0.093-0.212,0.139-0.332,0.139c-0.119,0-0.238-0.045-0.33-0.137l-2.154-2.153c-0.184-0.183-0.184-0.479,0-0.663s0.479-0.184,0.662,0l2.154,2.153C10.485,24.036,10.485,24.332,10.302,24.515zM10.912,21.918c-0.093,0.093-0.214,0.139-0.333,0.139c-0.12,0-0.24-0.045-0.33-0.137l-1.162-1.161c-0.184-0.183-0.184-0.479,0-0.66c0.184-0.185,0.48-0.187,0.664-0.003l1.161,1.162C11.095,21.438,11.095,21.735,10.912,21.918zM12.513,20.316c-0.092,0.092-0.211,0.138-0.332,0.138c-0.119,0-0.239-0.046-0.331-0.138l-1.159-1.16c-0.184-0.184-0.184-0.48,0-0.664s0.48-0.182,0.663,0.002l1.159,1.161C12.696,19.838,12.696,20.135,12.513,20.316zM22.25,21.917h-8.67l8.67-8.67V21.917zM22.13,10.7c-0.09,0.092-0.211,0.138-0.33,0.138c-0.121,0-0.242-0.046-0.334-0.138l-1.16-1.159c-0.184-0.183-0.184-0.479,0-0.663c0.182-0.183,0.479-0.183,0.662,0l1.16,1.159C22.312,10.221,22.313,10.517,22.13,10.7zM24.726,10.092c-0.092,0.092-0.213,0.137-0.332,0.137s-0.24-0.045-0.33-0.137l-2.154-2.154c-0.184-0.183-0.184-0.481,0-0.664s0.482-0.181,0.664,0.002l2.154,2.154C24.911,9.613,24.909,9.91,24.726,10.092z');
				break;

			case 'calendar':
				figure = dViewer.paper.path('M22,4.582h-2v3.335h2V4.582zM12,4.582h-2v3.335h2V4.582zM25.416,5.748H23v3.17h-4v-3.17h-6v3.168H9.002V5.748H6.583v21.555h18.833V5.748zM11.033,26.303H7.584v-3.44h3.449V26.303zM11.033,21.862H7.584v-3.434h3.449V21.862zM11.033,17.429H7.584v-3.441h3.449V17.429zM15.501,26.303h-3.468v-3.44h3.468V26.303zM15.501,21.862h-3.468v-3.434h3.468V21.862zM15.501,17.429h-3.468v-3.441h3.468V17.429zM19.97,26.303h-3.469v-3.44h3.469V26.303zM19.97,21.862h-3.469v-3.434h3.469V21.862zM19.97,17.429h-3.469v-3.441h3.469V17.429zM24.418,26.303H20.97v-3.44h3.448V26.303zM24.418,21.862H20.97v-3.434h3.448V21.862zM24.418,17.429H20.97v-3.441h3.448V17.429z');
				break;
		}
		
		figure.attr({
			'fill': "#1E5799", 
			'stroke': "none", 
			'stroke-width': 3
		});
		figure.hide();
		dViewer.paper.createElement = figure;
		dViewer.paper.setProperties(figure);
	},
	textAnnotationTool: function(s){
		var dViewer = this;
		if(s){dViewer.mode = 'createTextAnnotation'; dViewer.setCursorOnCanvas('crosshair');}
		else{dViewer.mode = 'default'; dViewer.setCursorOnCanvas('default');}
	}
});

sw.Promed.EvnDirectionAllInfoPanel = Ext.extend(sw.Promed.Panel,
{
	autoHeight: true,
	border: true,
	frame: true,
	collapsible: true,
	layout: 'form',
	style: 'margin-bottom: 0.5em;',
	title: langs('1. По направлению'),
	parentClass: null,
	personFieldName: 'Person_id',
	evnFieldName: null,
	idFieldName: 'EvnDirection_id',
	fieldIsAutoName: 'EvnDirection_IsAuto',
	medStaffFactFieldName: null,
	timeTableGrafFieldName: null,
	isLoaded: false,
	listeners: {
		'expand': function(panel) {
			if ( panel.isLoaded === false ) {
				panel.isLoaded = true;
			}
			//panel.doLayout();
		}
	},
	getBaseForm: function()
	{
		return this.findParentByType('form').getForm();// === this.up('form').getForm();
	},
	onReset: function(win)
	{
		this.collapse();
		this.DirectionInfoData.getStore().removeAll();
		this.isLoaded = false;
		if (this.initialConfig.hidden) {
			this.hide();
		}
	},
	onLoadForm: function(win, callback)
	{
		var me = this,
			bf = this.getBaseForm(),
			params = {
				useCase: 'load_evn_direction_all_info_panel',
				parentClass: me.parentClass,
				Person_id: bf.findField(me.personFieldName).getValue()
			};
		if (me.idFieldName) {
			params.EvnDirection_id = bf.findField(me.idFieldName).getValue() || null;
		}
		if (me.timeTableGrafFieldName) {
			params.TimetableGraf_id = bf.findField(me.timeTableGrafFieldName).getValue() || null;
		}
		if (me.medStaffFactFieldName) {
			params.MedStaffFact_id = bf.findField(me.medStaffFactFieldName).getValue() || null;
		}
		if (me.evnFieldName) {
			params.Evn_id = bf.findField(me.evnFieldName).getValue() || null;
		}
		if (params.EvnDirection_id
			|| params.TimetableGraf_id 
			|| (params.Evn_id && params.parentClass && params.parentClass.inlist(['EvnPL','EvnPLStom']))
		) {
			me.DirectionInfoData.getStore().load(
			{
				params: params,
				callback: function()
				{
					if (me.DirectionInfoData.getStore().getCount()>0) {
						// Экспандим панель
						if (me.initialConfig.hidden) {
							me.show();
						}
						me.expand();
						// устанавливаем полученный EvnDirection_id 
						if (me.idFieldName && !params.EvnDirection_id) {
							bf.findField(me.idFieldName).setValue(me.DirectionInfoData.getFieldValue('EvnDirection_id'));
						}
						if (me.fieldIsAutoName && !params.EvnDirection_id) {
							bf.findField(me.fieldIsAutoName).setValue(me.DirectionInfoData.getFieldValue('EvnDirection_IsAuto'));
						}
					}
					var EvnDirection_id = bf.findField(me.idFieldName).getValue() || null,
						EvnDirection_IsAuto = me.DirectionInfoData.getFieldValue('EvnDirection_IsAuto') || 1;
					if (0 == me.DirectionInfoData.getStore().getCount() || !EvnDirection_id || 2 == EvnDirection_IsAuto) {
						// Коллапсим и скрываем панель
						me.collapse();
						if (me.initialConfig.hidden) {
							me.hide();
						}
					}
					if (callback) {
						callback(EvnDirection_id, EvnDirection_IsAuto);
					}
				}
			});
		}
	},
	initComponent: function()
	{
		this.DirectionInfoData = new Ext.DataView({
			border: false,
			frame: false,
			itemSelector: 'div',
			layout: 'fit',
			getFieldValue: function(field) 
			{
				var result = '';
				if (this.getStore().getAt(0))
					result = this.getStore().getAt(0).get(field);
				return result;
			},
			store: new Ext.data.JsonStore({
				autoLoad: false,
				fields: [
					{name: 'EvnDirection_id'}, 
					{name: 'EvnDirection_IsAuto'}, 
					{name: 'EvnDirection_IsReceive'},// Внешнее направление
					{name: 'EvnDirection_Num'}, // Номер
					{name: 'EvnDirection_setDate', dateFormat: 'd.m.Y', type: 'date'}, // Дата 
					{name: 'DirType_id'},
					{name: 'DirType_Name'}, // Тип направления 
					{name: 'Lpu_Name'}, // направившее ЛПУ
					{name: 'LpuSectionProfile_id'},
					{name: 'LpuSectionProfile_Code'}, // Профиль
					{name: 'LpuSectionProfile_Name'}, // Профиль
					{name: 'Timetable_begTime'}, // Время записи
					{name: 'Diag_id'},
					{name: 'Diag_Name'}, 
					{name: 'EvnDirection_Descr'}, // Описание 
					{name: 'MedStaffFact_id'}, 
					{name: 'MedPersonal_id'}, 
					{name: 'MedPersonal_Fio'}, // Врач 
					{name: 'MedPersonal_zid'}, 
					{name: 'MedPersonal_zFio'} // Зав.отделением
				],
				url: '/?c=EvnDirection&m=loadEvnDirectionList'
			}),
			tpl: new Ext.XTemplate(
				'<tpl for=".">',
				'<div>Направление №<font style="color: blue; font-weight: bold;">{EvnDirection_Num}</font>, выписано: <font style="color: blue;">{[Ext.util.Format.date(values.EvnDirection_setDate, "d.m.Y")]}</font>, тип направления: <font style="color: blue;">{DirType_Name}</font> </div>',
				'<div>ЛПУ направления: <font style="color: blue;">{Lpu_Name}</font>, по профилю: <font style="color: blue;">{LpuSectionProfile_Code}.{LpuSectionProfile_Name}</font> ',
				'<div>Диагноз: <font style="color: blue;">{Diag_Name}</font></div>',
				'<div>Врач: <font style="color: blue;">{MedPersonal_Fio}</font>,  Зав.отделением: <font style="color: blue;">{MedPersonal_zFio}</font></div>',
				'<div>Время записи: <font style="color: blue;">{Timetable_begTime}</font>',
				'</tpl>'
			)
		});
		this.items = [this.DirectionInfoData];
		sw.Promed.EvnDirectionAllInfoPanel.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swevndirectionallinfopanel', sw.Promed.EvnDirectionAllInfoPanel);

sw.Promed.EvnDirectionAllPanel = Ext.extend(Ext.Panel,
{
	prefix: '',
	startTabIndex: 0,
	useCase: null,
	personPanelId: null,
	personFieldName: null,
	fromLpuFieldName: null,
	fieldIsWithDirectionName: null,
	buttonSelectId: null,
	parentSetDateFieldName: null,
	nextFieldName: null,
	showMedStaffFactCombo: false,
	fieldPrehospDirectName: 'PrehospDirect_id',
	fieldLpuSectionName: 'LpuSection_did',
	fieldMedStaffFactName: 'MedStaffFact_did',
	fieldOrgName: 'Org_did',
	fieldNumName: 'EvnDirection_Num',
	fieldSetDateName: 'EvnDirection_setDate',
	fieldDiagName: 'Diag_did',
	fieldDiagFName:'Diag_fid',
	fieldDiagPreidName:'Diag_preid',
	fieldIdName: 'EvnDirection_id',
	fieldIsAutoName: 'EvnDirection_IsAuto',
	fieldIsExtName: 'EvnDirection_IsReceive',
	fieldDoctorCode: 'MedPersonalCode',
	medStaffFactFieldName: null,
	//fieldTimaTableName: null,
	//fieldEvnPrescrName: null,
	isReadOnly: false,//можно ли заполнять поля
	isDisabledChooseDirection: false,//можно ли выбрать электронное направление
	border: false,
	layout: 'form',
	getBaseForm: function()
	{
		return this.findParentByType('form').getForm();// === this.up('form').getForm();
	},
	getBaseComp: function()
	{
		return this.findParentByType('form');// === this.up('form').getForm();
	},
	onReset: function()
	{
		this.isReadOnly = false;
		this.isDisabledChooseDirection = false;
		var me = this, base_form = me.getBaseForm(),
			iswd_combo = me.getIsWithDirectionField();
		setLpuSectionGlobalStoreFilter();
		base_form.findField(me.fieldLpuSectionName).getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		setMedStaffFactGlobalStoreFilter();
		base_form.findField(me.fieldMedStaffFactName).getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		base_form.findField(me.fieldMedStaffFactName).setContainerVisible(me.showMedStaffFactCombo);
		me._applyEvnDirectionData(null);
		iswd_combo.setValue(1);
		iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
	},
	onBeforeSubmit: function(win, params)
	{
		var me = this,
			base_form = me.getBaseForm(),
			org_combo = base_form.findField(me.fieldOrgName),
			diag_combo = base_form.findField(me.fieldDiagName),
			evn_direction_set_date_field = base_form.findField(me.fieldSetDateName),
			evn_direction_num_field = base_form.findField(me.fieldNumName),
			lpu_section_combo = base_form.findField(me.fieldLpuSectionName),
			med_staff_fact_combo = base_form.findField(me.fieldMedStaffFactName),
			prehosp_direct_combo = base_form.findField(me.fieldPrehospDirectName),
			evn_direction_id_field = base_form.findField(me.fieldIdName),
			iswd_combo = me.getIsWithDirectionField();

		if (!getRegionNick().inlist(['buryatiya', 'ekb', 'kaluga', 'kareliya', 'krym', 'perm'])
			&& me.useCase.inlist([
				'choose_for_evnplstom'
				,'choose_for_evnpl'
				,'choose_for_evnpl_stream_input'
				,'choose_for_evnplstom_stream_input'
			]) 
			&& iswd_combo.getValue() == 2
			&& prehosp_direct_combo.getValue() == 2
			&& Ext.isEmpty(evn_direction_id_field.getValue())
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					prehosp_direct_combo.focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: langs('При выбранном значении "Другое ЛПУ" в поле "Кем направлен" выбор электронного направления является обязательным'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if ( iswd_combo.disabled ) {
			params[me.fieldIsWithDirectionName] = iswd_combo.getValue();
		}
		if ( prehosp_direct_combo.disabled ) {
			params[me.fieldPrehospDirectName] = prehosp_direct_combo.getValue();
		}
		if ( lpu_section_combo.disabled ) {
			params[me.fieldLpuSectionName] = lpu_section_combo.getValue();
		}
		if ( med_staff_fact_combo.disabled ) {
			params[me.fieldMedStaffFactName] = med_staff_fact_combo.getValue();
		}
		if ( org_combo.disabled ) {
			params[me.fieldOrgName] = org_combo.getValue();
		}
		if ( diag_combo.disabled ) {
			params[me.fieldDiagName] = diag_combo.getValue();
		}
		if ( evn_direction_num_field.disabled ) {
			params[me.fieldNumName] = evn_direction_num_field.getRawValue();
		}
		params[me.fieldSetDateName] = Ext.util.Format.date(evn_direction_set_date_field.getValue(), 'd.m.Y');
		return params;
	},
	onLoadForm: function(win)
	{
		var me = this,
			base_form = me.getBaseForm(),
			prehosp_direct_combo = base_form.findField(me.fieldPrehospDirectName),
			evn_direction_data = {
				PrehospDirect_id: prehosp_direct_combo.getValue(),
				Lpu_sid: base_form.findField(me.fromLpuFieldName).getValue(),
				EvnDirection_id: base_form.findField(me.fieldIdName).getValue(),
				EvnDirection_IsAuto: base_form.findField(me.fieldIsAutoName).getValue(),
				EvnDirection_IsReceive: base_form.findField(me.fieldIsExtName).getValue(),
				EvnDirection_Num: base_form.findField(me.fieldNumName).getValue(),
				EvnDirection_setDate: base_form.findField(me.fieldSetDateName).getValue(),
				LpuSection_id: base_form.findField(me.fieldLpuSectionName).getValue(),
				MedStaffFact_id: base_form.findField(me.fieldMedStaffFactName).getValue(),
				Org_did: base_form.findField(me.fieldOrgName).getValue(),
				Diag_did: base_form.findField(me.fieldDiagName).getValue(),
				Diag_fid: base_form.findField(me.fieldDiagFName).getValue(),
				Diag_preid: base_form.findField(me.fieldDiagPreidName).getValue()
			};

		if (prehosp_direct_combo.getStore().getCount() == 0) {
			prehosp_direct_combo.getStore().load({
				callback: function(){
					me._applyEvnDirectionData(evn_direction_data);
				}
			});
		} else {
			me._applyEvnDirectionData(evn_direction_data);
		}
	},
	_applyEvnDirectionData: function(data, notDisableChoose)
	{
		var me = this,
			base_form = me.getBaseForm(),
			from_lpu_field = base_form.findField(me.fromLpuFieldName),
			diag_combo = base_form.findField(me.fieldDiagName),
			evn_direction_id_field = base_form.findField(me.fieldIdName),
			evn_direction_isauto_field = base_form.findField(me.fieldIsAutoName),
			evn_direction_isext_field = base_form.findField(me.fieldIsExtName),
			evn_direction_set_date_field = base_form.findField(me.fieldSetDateName),
			evn_direction_num_field = base_form.findField(me.fieldNumName),
			lpu_section_combo = base_form.findField(me.fieldLpuSectionName),
			med_staff_fact_combo = base_form.findField(me.fieldMedStaffFactName),
			diag_f_combo = base_form.findField(me.fieldDiagFName),
			fieldDiagPreidName = base_form.findField(me.fieldDiagPreidName),
			org_combo = base_form.findField(me.fieldOrgName),
			prehosp_direct_combo = base_form.findField(me.fieldPrehospDirectName),
			iswd_combo = me.getIsWithDirectionField();

		from_lpu_field.setValue(null);
		diag_combo.setValue(null);
		diag_f_combo.setValue(null),
		fieldDiagPreidName.setValue(null),
		evn_direction_id_field.setValue(null);
		evn_direction_isauto_field.setValue(null);
		evn_direction_isext_field.setValue(null);
		evn_direction_set_date_field.setValue(null);
		evn_direction_num_field.setValue('');
		lpu_section_combo.setValue(null);
		med_staff_fact_combo.setValue(null);
		org_combo.setValue(null);
		prehosp_direct_combo.setValue(null);
		// Убираем признак обязательности заполнения с полей
		lpu_section_combo.setAllowBlank(true);
		med_staff_fact_combo.setAllowBlank(true);
		org_combo.setAllowBlank(true);
		diag_combo.setAllowBlank(true);
		evn_direction_set_date_field.setAllowBlank(true);
		evn_direction_num_field.setAllowBlank(true);
		fieldDiagPreidName.setContainerVisible(false);
		fieldDiagPreidName.setDisabled(true);
		//
		log('_applyEvnDirectionData', data);
		if (!notDisableChoose) { // Если электронное направление в талоне выбрано впервые, то до момента сохранения талона возможен выбор другого электронного направления.
			me.isDisabledChooseDirection = me.isReadOnly || (data && data.EvnDirection_id && data.EvnDirection_id > 0);
		}
		me.isReadOnly = me.isReadOnly || (data && data.EvnDirection_id && data.EvnDirection_id > 0 && 2 != data.EvnDirection_IsAuto);
		me.findById(me.buttonSelectId).setDisabled(me.isDisabledChooseDirection);
		iswd_combo.setDisabled(me.isDisabledChooseDirection);
		prehosp_direct_combo.setDisabled(me.isReadOnly);
		org_combo.setDisabled(me.isReadOnly);
		lpu_section_combo.setDisabled(me.isReadOnly);
		med_staff_fact_combo.setDisabled(me.isReadOnly);
		evn_direction_num_field.setDisabled(me.isReadOnly);
		evn_direction_set_date_field.setDisabled(me.isReadOnly);
		diag_combo.setDisabled(me.isReadOnly);
		if (data) {
			if (2 == data.EvnDirection_IsAuto) {
				// информацию из направления не подтягиваем
				/*
				data.EvnDirection_Num = '';
				data.EvnDirection_setDate = '';
				data.LpuSection_id = null;
				data.MedStaffFact_id = null;
				data.Org_did = null;
				data.Diag_did = null;
				data.Lpu_sid = null;
				*/
			}
			var PrehospDirect_id = null;
			if (data.PrehospDirect_id) {
				PrehospDirect_id = data.PrehospDirect_id;
			} else {
				PrehospDirect_id = sw.Promed.EvnDirectionAllPanel.calcPrehospDirectId(data.Lpu_sid, data.Org_did, data.LpuSection_id, data.EvnDirection_IsAuto);
			}
			prehosp_direct_combo.setValue(PrehospDirect_id);
			evn_direction_id_field.setValue(data.EvnDirection_id||null);
			evn_direction_isauto_field.setValue(data.EvnDirection_IsAuto||null);
			evn_direction_isext_field.setValue(data.EvnDirection_IsReceive||null);
			from_lpu_field.setValue(data.Lpu_sid||null);
			evn_direction_num_field.setValue(data.EvnDirection_Num||'');
			evn_direction_set_date_field.setValue(data.EvnDirection_setDate);
			if ( data.Diag_did ) {
				diag_combo.getStore().load({
					callback: function() {
						diag_combo.getStore().each(function(record) {
							if ( record.get('Diag_id') == data.Diag_did ) {
								diag_combo.setValue(data.Diag_did);
								diag_combo.fireEvent('select', diag_combo, record, 0);
								diag_combo.fireEvent('change', diag_combo, data.Diag_did);
							}
						});
						if(data.Diag_preid){
							fieldDiagPreidName.getStore().load({
								callback: function() {
									fieldDiagPreidName.getStore().each(function(record) {
										if ( record.get('Diag_id') == data.Diag_preid ) {
											fieldDiagPreidName.setValue(data.Diag_preid);
											fieldDiagPreidName.fireEvent('select', fieldDiagPreidName, record, 0);
										}
									})
								},
								params: {
									where: "where DiagLevel_id = 4 and Diag_id = " + data.Diag_preid
								}
							})
						}
					},
					params: {
						where: "where DiagLevel_id = 4 and Diag_id = " + data.Diag_did
					}
				});
			}
			if (me.isDisabledChooseDirection) {
				iswd_combo.setValue(me.defineIsWithDirectionValue());
				var prehosp_direct_sysnick = prehosp_direct_combo.getFieldValue('PrehospDirect_SysNick');
				var org_type = '';
				switch (prehosp_direct_sysnick) {
					case 'lpusection':
					case 'OtdelMO':
						if (data.LpuSection_id) {
							lpu_section_combo.setValue(data.LpuSection_id);
						}
						if (data.MedStaffFact_id) {
							med_staff_fact_combo.setValue(data.MedStaffFact_id);
						}
						break;
					case 'lpu':
					case 'skor':
					case 'DrMO':
					case 'Skor':
						org_type = 'lpu';
						break;
					case 'rvk':
					case 'Rvk':
						org_type = 'military';
						break;
					case 'org':
					case 'admin':
					case 'Pmsp':
					case 'Kdp':
					case 'Stac':
					case 'Rdom':
						org_type = 'org';
						break;
				}
				if ( data.Org_did ) {
					org_combo.getStore().load({
						callback: function(records, options, success) {
							if ( success ) {
								org_combo.setValue(data.Org_did);
								me.checkOtherLpuDirection();
							}
						},
						params: {
							Org_id: data.Org_did,
							OrgType: org_type
						}
					});

					if (data.LpuSection_id) {
						lpu_section_combo.getStore().load({
							params: {
								Org_id: data.Org_did,
								mode: 'combo'
							},
							callback: function () {
								lpu_section_combo.setValue(data.LpuSection_id);
							}
						});
					}

					if (data.MedStaffFact_id) {
						med_staff_fact_combo.getStore().load({
							params: {
								Org_id: data.Org_did,
								andWithoutLpuSection: 3,
								mode: 'combo'
							},
							callback: function () {
								med_staff_fact_combo.setValue(data.MedStaffFact_id);
							}
						});
					}
				} else if (data.Lpu_sid) {
					org_combo.getStore().load({
						callback: function(records, options, success) {
							if ( success ) {
								if (records.length == 1)  {
									org_combo.setValue(records[0].get('Org_id'));
									me.checkOtherLpuDirection();
								}
							}
						},
						params: {
							Lpu_oid: data.Lpu_sid,
							OrgType: org_type
						}
					});

					if (data.LpuSection_id) {
						lpu_section_combo.getStore().load({
							params: {
								Lpu_id: data.Lpu_sid,
								LpuSection_id: data.LpuSection_id,
								mode: 'combo'
							},
							callback: function () {
								lpu_section_combo.setValue(data.LpuSection_id);
							}
						});
					}

					if (data.MedStaffFact_id) {
						med_staff_fact_combo.getStore().load({
							params: {
								Lpu_id: data.Lpu_sid,
								MedStaffFact_id: data.MedStaffFact_id,
								andWithoutLpuSection: 3,
								mode: 'combo'
							},
							callback: function () {
								med_staff_fact_combo.setValue(data.MedStaffFact_id);
							}
						});
					}
				}
			} else {
				org_combo.setValue(data.Org_did||null);
				lpu_section_combo.setValue(data.LpuSection_id||null);
				med_staff_fact_combo.setValue(data.MedStaffFact_id||null);
				fieldDiagPreidName.setValue(data.Diag_preid||null);
				iswd_combo.setValue(me.defineIsWithDirectionValue());
				iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
			}
		}
	},
	openEvnDirectionSelectWindow: function()
	{
		if ( this.isDisabledChooseDirection ) {
			return false;
		}
		var me = this,
			base_form = me.getBaseForm(),
			person_info = Ext.getCmp(me.personPanelId);
		// По кнопке “Выбор направления” всегда вызывать форму выбора со скрытым нижним гридом “Записи”
		if ( getWnd('swEvnDirectionSelectWindow').isVisible() ) {
			getWnd('swEvnDirectionSelectWindow').hide();
		}
		getWnd('swEvnDirectionSelectWindow').show({
			callback: function(evnDirectionData) {
				if (evnDirectionData && evnDirectionData.EvnDirection_id){
					// создавать случай со связью с направлением
					me._applyEvnDirectionData(evnDirectionData);
				} else {
					// создать случай без связи с направлением
					me._applyEvnDirectionData(null);
				}
			},
			onDate: me.parentSetDateFieldName ? base_form.findField(me.parentSetDateFieldName).getValue() : getGlobalOptions().date,
			onHide: function() {
				base_form.findField(me.nextFieldName).focus(true);
			},
			useCase: me.useCase,
			MedStaffFact_id: me.medStaffFactFieldName ? base_form.findField(me.medStaffFactFieldName).getValue() : getGlobalOptions().CurMedStaffFact_id,
			Person_Birthday: person_info.getFieldValue('Person_Birthday'),
			Person_Firname: person_info.getFieldValue('Person_Firname'),
			Person_id: base_form.findField(me.personFieldName).getValue(),
			Person_Secname: person_info.getFieldValue('Person_Secname'),
			Person_Surname: person_info.getFieldValue('Person_Surname')
		});
		return true;
	},
	defineIsWithDirectionValue: function()
	{
		var me = this, base_form = me.getBaseForm();
		return sw.Promed.EvnDirectionAllPanel.isWithDirection(base_form.findField(me.fieldIdName).getValue(), base_form.findField(me.fieldIsAutoName).getValue(), base_form.findField(me.fieldIsExtName).getValue(), base_form.findField(me.fieldNumName).getValue());
	},
	checkOtherLpuDirection: Ext.emptyFn,
	initComponent: function()
	{
		var me = this;
		var iswd_combo = new sw.Promed.SwYesNoCombo({
			fieldLabel: langs('С электронным направлением'),
			hiddenName: me.fieldIsWithDirectionName,
			value: 1,
			allowBlank: false,
			tabIndex: me.startTabIndex,
			width: 60,
			listeners: 
			{
				'change': function (combo, newValue, oldValue) 
				{
					if (false == me.isDisabledChooseDirection) {
						var base_form = me.getBaseForm();
						// запрещаем редактировать, если выбрано эл. направление
						var evn_direction_id = base_form.findField(me.fieldIdName).getValue();
						base_form.findField(me.fieldDiagName).setDisabled(newValue == 2);
						base_form.findField(me.fieldSetDateName).setDisabled(newValue == 2);
						base_form.findField(me.fieldNumName).setDisabled(newValue == 2);
						base_form.findField(me.fieldLpuSectionName).setDisabled(newValue == 2);
						base_form.findField(me.fieldMedStaffFactName).setDisabled(newValue == 2);
						base_form.findField(me.fieldOrgName).setDisabled(newValue == 2);
						var prehosp_direct_combo = base_form.findField(me.fieldPrehospDirectName);
						prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, prehosp_direct_combo.getValue());
					}
				},
				'select': function(combo, record, index) {
					combo.enabledSetValue = false;
					combo.fireEvent('change', combo, record.get(combo.valueField));
				}
			}
		});
		me.getIsWithDirectionField = function() { return iswd_combo; };
		me.items = [{
			border: false,
			layout: 'column',
			items: [{
				border: false,
				layout: 'form',
				width: 290,
				items: [iswd_combo]
			}, {
				border: false,
				layout: 'form',
				width: 200,
				items: [{
					handler: function() {
						me.openEvnDirectionSelectWindow();
					},
					icon: 'img/icons/add16.png',
					iconCls: 'x-btn-text',
					id: me.buttonSelectId,
					tabIndex: me.startTabIndex + 1,
					text: langs('Выбрать направление'),
					tooltip: langs('Выбор направления'),
					xtype: 'button'
				}]
			}]
		}, {
			comboSubject: 'PrehospDirect',
			allowSysNick: true,
			typeCode: 'int',
			fieldLabel: langs('Кем направлен'),
			hiddenName: me.fieldPrehospDirectName,
			lastQuery: '',
			listeners: {
				'change': function(combo, newValue, oldValue) {
					var base_form = me.getBaseForm(),
						diag_combo = base_form.findField(me.fieldDiagName),
						evn_direction_id_field = base_form.findField(me.fieldIdName),
						fieldDiagPreidName = base_form.findField(me.fieldDiagPreidName),
						evn_direction_isauto_field = base_form.findField(me.fieldIsAutoName),
						evn_direction_isext_field = base_form.findField(me.fieldIsExtName),
						evn_direction_set_date_field = base_form.findField(me.fieldSetDateName),
						evn_direction_num_field = base_form.findField(me.fieldNumName),
						lpu_section_combo = base_form.findField(me.fieldLpuSectionName),
						med_staff_fact_combo = base_form.findField(me.fieldMedStaffFactName),
						org_combo = base_form.findField(me.fieldOrgName),
						iswd_combo = me.getIsWithDirectionField(),
						record = combo.getStore().getById(newValue),
						prehosp_direct_sysnick;
					
					if ( record ) {
						prehosp_direct_sysnick = record.get('PrehospDirect_SysNick');
					}
					var isDisabledChooseDirection = me.isDisabledChooseDirection;
					var isReadOnly = me.isReadOnly;
					var iswd_value = iswd_combo.getValue();
					var evn_direction_id = evn_direction_id_field.getValue();
					var evn_direction_isauto = evn_direction_isauto_field.getValue();
					var evn_direction_isext = evn_direction_isext_field.getValue();
					var evn_direction_set_date = evn_direction_set_date_field.getValue();
					var evn_direction_num = evn_direction_num_field.getValue();
					var lpu_section_id = lpu_section_combo.getValue();
					var med_staff_fact_id = med_staff_fact_combo.getValue();
					var org_id = org_combo.getValue();
					var diag_id = diag_combo.getValue();

					lpu_section_combo.clearValue();
					med_staff_fact_combo.clearValue();

					//me._applyEvnDirectionData(null);
					me.isDisabledChooseDirection = isDisabledChooseDirection;
					me.isReadOnly = isReadOnly;
					me.findById(me.buttonSelectId).setDisabled(me.isDisabledChooseDirection);
					iswd_combo.setDisabled(me.isDisabledChooseDirection);

					if ( prehosp_direct_sysnick == null ) {
						diag_combo.disable();
						evn_direction_set_date_field.disable();
						evn_direction_num_field.disable();
						lpu_section_combo.disable();
						med_staff_fact_combo.disable();
						
						org_combo.disable();
						// созданные регистратором автонаправления не являются направлением от отделения ЛПУ. Для таких направлений "Кем направлен" нужно оставить пустым
						if (evn_direction_id && 1 == iswd_value) {
							evn_direction_id_field.setValue(evn_direction_id);
							evn_direction_isauto_field.setValue(evn_direction_isauto||1);
							evn_direction_isext_field.setValue(evn_direction_isext||1);
							evn_direction_set_date_field.setValue(evn_direction_set_date);
							evn_direction_num_field.setValue(evn_direction_num);
							diag_combo.setValue(diag_id);
							if(diag_id){
								diag_combo.fireEvent('change', diag_combo,diag_id);
							}
							fieldDiagPreidName.setValue(fieldDiagPreidName.getValue());
						}
						return false;
					}
					combo.setValue(newValue);
					if (prehosp_direct_sysnick && prehosp_direct_sysnick.inlist(['lpusection', 'lpu'])) {
						iswd_combo.setValue(iswd_value);
						if (evn_direction_id||evn_direction_num) {
							evn_direction_id_field.setValue(evn_direction_id||null);
							evn_direction_isauto_field.setValue(evn_direction_isauto||1);
							evn_direction_isext_field.setValue(evn_direction_isext||1);
							evn_direction_set_date_field.setValue(evn_direction_set_date);
							evn_direction_num_field.setValue(evn_direction_num);
							diag_combo.setValue(diag_id);
							if(diag_id){
								diag_combo.fireEvent('change', diag_combo,diag_id);
							}
							fieldDiagPreidName.setValue(fieldDiagPreidName.getValue());
						}
					}

					switch ( prehosp_direct_sysnick ) {
						case 'lpusection':
						case 'OtdelMO':
							if ( lpu_section_id ) {
								lpu_section_combo.setValue(lpu_section_id);
							}
							if ( med_staff_fact_id ) {
								med_staff_fact_combo.setValue(med_staff_fact_id);
							}
							evn_direction_set_date_field.setDisabled(me.isReadOnly);
							evn_direction_num_field.setDisabled(me.isReadOnly);
							diag_combo.setDisabled(me.isReadOnly);
							lpu_section_combo.setDisabled(me.isReadOnly);
							lpu_section_combo.setAllowBlank(getRegionNick() == 'kz');
							med_staff_fact_combo.setDisabled(me.isReadOnly);
							if (med_staff_fact_combo.isVisible() && getRegionNick() != 'kz') {
								med_staff_fact_combo.setAllowBlank(false);
							} else {
								med_staff_fact_combo.setAllowBlank(true);
							}
							org_combo.reset();
							if (me.useCase.inlist(['choose_for_evnpl','choose_for_evnpl_stream_input'])) {
								lpu_section_combo.reset();
								med_staff_fact_combo.reset();
								setLpuSectionGlobalStoreFilter();
								lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
								setMedStaffFactGlobalStoreFilter();
								med_staff_fact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

								record = lpu_section_combo.getStore().getById(lpu_section_id);
								if (record) {
									lpu_section_combo.setValue(lpu_section_id);
								}
								record = med_staff_fact_combo.getStore().getById(med_staff_fact_id);
								if (record) {
									med_staff_fact_combo.setValue(med_staff_fact_id);
								}
							}
							org_combo.setDisabled(true);
							if (getRegionNick() != 'ekb') {
								evn_direction_set_date_field.setAllowBlank(true);
								evn_direction_num_field.setAllowBlank(true);
							}
							diag_combo.setAllowBlank(true);
						break;

						case 'lpu':
						case 'DrMO':
						case 'Pmsp':
							if ( org_id ) {
								org_combo.setValue(org_id);
							}
							evn_direction_set_date_field.setDisabled(me.isReadOnly);
							evn_direction_num_field.setDisabled(me.isReadOnly);
							diag_combo.setDisabled(me.isReadOnly);
							lpu_section_combo.setDisabled(me.isReadOnly);
							med_staff_fact_combo.setDisabled(me.isReadOnly);
							org_combo.setDisabled(me.isReadOnly);
							org_combo.setAllowBlank(getRegionNick() == 'kz');
							if (me.useCase.inlist(['choose_for_evnpl','choose_for_evnpl_stream_input'])) {
								lpu_section_combo.reset();
								med_staff_fact_combo.reset();
								lpu_section_combo.setDisabled(evn_direction_id && 2 == iswd_value);
								lpu_section_combo.setAllowBlank(true);
								med_staff_fact_combo.setDisabled(evn_direction_id && 2 == iswd_value);
								med_staff_fact_combo.setAllowBlank(true);

								base_form.findField(me.fieldLpuSectionName).getStore().removeAll();
								base_form.findField(me.fieldMedStaffFactName).getStore().removeAll();

								if (org_id) {
									base_form.findField(me.fieldLpuSectionName).getStore().load({
										params: {
											Org_id: org_id,
											mode: 'combo'
										},
										callback: function(){
											base_form.findField(me.fieldLpuSectionName).setValue(lpu_section_id);
										}
									});
									base_form.findField(me.fieldMedStaffFactName).getStore().load({
										params: {
											Org_id: org_id,
											andWithoutLpuSection: 3,
											mode: 'combo'
										},
										callback: function(){
											base_form.findField(me.fieldMedStaffFactName).setValue(med_staff_fact_id);
										}
									});
								}
							}

							if (!getRegionNick().inlist([ 'buryatiya' ]) && me.useCase.inlist([
								'choose_for_evnplstom'
								,'choose_for_evnpl'
								,'choose_for_evnpl_stream_input'
								,'choose_for_evnplstom_stream_input'
							]) && !me.isReadOnly) {
								diag_combo.setAllowBlank(false);
								evn_direction_set_date_field.setAllowBlank(false);
								evn_direction_num_field.setAllowBlank(false);

								me.checkOtherLpuDirection();
							}
						break;

						case 'org':
						case 'rvk':
						case 'skor':
						case 'admin':
						case 'Kdp':
						case 'Skor':
						case 'Stac':
						case 'Rvk':
						case 'Rdom':
							if ( org_id ) {
								org_combo.setValue(org_id);
							}
							evn_direction_set_date_field.setDisabled(me.isReadOnly);
							evn_direction_num_field.setDisabled(me.isReadOnly);
							diag_combo.setDisabled(me.isReadOnly);
							lpu_section_combo.setDisabled(true);
							med_staff_fact_combo.setDisabled(true);
							org_combo.setDisabled(me.isReadOnly);
							org_combo.setAllowBlank(true);
							evn_direction_set_date_field.setAllowBlank(true);
							evn_direction_num_field.setAllowBlank(true);
							diag_combo.setAllowBlank(true);
						break;

						default:
							evn_direction_set_date_field.setDisabled(true);
							evn_direction_num_field.setDisabled(true);
							diag_combo.setDisabled(true);
							lpu_section_combo.setDisabled(true);
							med_staff_fact_combo.setDisabled(true);
							org_combo.setDisabled(true);
						break;
					}
					
					if ( org_combo.getValue() && !org_combo.getStore().getById(org_combo.getValue())) {
						var org_type = '';
						switch ( prehosp_direct_sysnick ) {
							case 'lpu':
							case 'skor':
							case 'DrMO':
							case 'Skor':
								org_type = 'lpu';
								break;
							case 'rvk':
							case 'Rvk':
								org_type = 'military';
								break;
							case 'org':
							case 'admin':
							case 'Pmsp':
							case 'Kdp':
							case 'Stac':
							case 'Rdom':
								org_type = 'org';
								break;
						}
						org_combo.getStore().load({
							callback: function(records, options, success) {
								if ( success ) {
									org_combo.setValue(org_combo.getValue());
								}
							},
							params: {
								Org_id: org_combo.getValue(),
								OrgType: org_type
							}
						});
					}
					
					if ( diag_combo.getValue() && !diag_combo.getStore().getById(diag_combo.getValue())) {
						diag_combo.getStore().load({
							callback: function() {
								diag_combo.getStore().each(function(record) {
									if ( record.get('Diag_id') == diag_combo.getValue() ) {
										diag_combo.setValue(diag_combo.getValue());
										diag_combo.fireEvent('select', diag_combo, record, 0);
										diag_combo.fireEvent('change', diag_combo,diag_id);
									}
								});
								fieldDiagPreidName.setValue(fieldDiagPreidName.getValue());
							},
							
							params: {
								where: "where DiagLevel_id = 4 and Diag_id = " + diag_combo.getValue()
							}
						});
					}
				},
				'select': function(combo, record, index) {
					combo.fireEvent('change', combo, record.get(combo.valueField));
				}
			},
			tabIndex: me.startTabIndex + 2,
			width: 300,
			xtype: 'swcommonsprcombo'
		}, {
			displayField: getRegionNick()=='ekb' ? 'Org_Nick' : 'Org_Name',
			editable: false,
			enableKeyEvents: true,
			fieldLabel: langs('Организация'),
			hiddenName: me.fieldOrgName,
			listeners: {
				'keydown': function( inp, e ) {
					if ( inp.disabled )
						return true;

					if ( e.F4 == e.getKey() ) {
						if ( e.browserEvent.stopPropagation )
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if ( e.browserEvent.preventDefault )
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.returnValue = false;

						if ( Ext.isIE ) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						inp.onTrigger1Click();
						return false;
					}
					return true;
				},
				'keyup': function(inp, e) {
					if ( e.F4 == e.getKey() ) {
						if ( e.browserEvent.stopPropagation )
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if ( e.browserEvent.preventDefault )
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.returnValue = false;

						if ( Ext.isIE ) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						return false;
					}
					return true;
				}
			},
			mode: 'local',
			onTrigger1Click: function() {
				var base_form = me.getBaseForm();
				var combo = base_form.findField(me.fieldOrgName);
				if ( combo.disabled ) {
					return false;
				}
				var prehosp_direct_combo = base_form.findField(me.fieldPrehospDirectName);
				var prehosp_direct_id = prehosp_direct_combo.getValue();
				var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);
				if ( !record ) {
					return false;
				}
				var prehosp_direct_sysnick = record.get('PrehospDirect_SysNick');
				var org_type = '';
				switch (prehosp_direct_sysnick) {
					case 'lpu':
					case 'skor':
					case 'DrMO':
					case 'Skor':
						org_type = 'lpu';
						break;
					case 'rvk':
					case 'Rvk':
						org_type = 'military';
						break;
					case 'org':
					case 'admin':
					case 'Pmsp':
					case 'Kdp':
					case 'Stac':
					case 'Rdom':
						org_type = 'org';
						break;
					default:
						return false;
						break;
				}
				getWnd('swOrgSearchWindow').show({
					object: org_type,
					onClose: function() {
						combo.focus(true, 200)
					},
					onDate: base_form.findField(me.fieldSetDateName).getValue(),
					onSelect: function(org_data) {
						if ( org_data.Org_id > 0 ) {
							combo.getStore().loadData([{
								Org_id: org_data.Org_id,
								Org_Name: org_data.Org_Name,
								Org_Nick: org_data.Org_Nick
							}]);
							combo.setValue(org_data.Org_id);
							if (me.useCase.inlist(['choose_for_evnpl','choose_for_evnpl_stream_input'])) {
								var lpu_section_id = base_form.findField(me.fieldLpuSectionName).getValue();
								var med_staff_fact_id = base_form.findField(me.fieldMedStaffFactName).getValue();
								base_form.findField(me.fieldLpuSectionName).getStore().load({
									params: {
										Org_id: org_data.Org_id,
										mode: 'combo'
									},
									callback: function(){
										base_form.findField(me.fieldLpuSectionName).setValue(lpu_section_id);
									}
								});
								base_form.findField(me.fieldMedStaffFactName).getStore().load({
									params: {
										Org_id: org_data.Org_id,
										andWithoutLpuSection: 3,
										mode: 'combo'
									},
									callback: function(){
										base_form.findField(me.fieldMedStaffFactName).setValue(med_staff_fact_id);
									}
								});
							}
							getWnd('swOrgSearchWindow').hide();
							combo.collapse();
						}

						me.checkOtherLpuDirection();
					}
				});
				return true;
			},
			onTrigger2Click: function() {
				if ( !this.disabled ) this.clearValue();

				me.checkOtherLpuDirection();
			},
			store: new Ext.data.JsonStore({
				autoLoad: false,
				fields: [
					{name: 'Org_id', type: 'int'},
					{name: 'Org_Name', type: 'string'},
					{name: 'Org_Nick', type: 'string'}
				],
				key: 'Org_id',
				sortInfo: {
					field: getRegionNick()=='ekb' ? 'Org_Nick' : 'Org_Name'
				},
				url: C_ORG_LIST
			}),
			tabIndex: me.startTabIndex + 3,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				getRegionNick()=='ekb' ? '{Org_Nick}' : '{Org_Name}',
				'</div></tpl>'
				),
			trigger1Class: 'x-form-search-trigger',
			triggerAction: 'none',
			valueField: 'Org_id',
			width: 500,
			xtype: 'swbaseremotecombo'
		}, {
			id: me.prefix+'_EDAP_LpuSectionCombo',
			hiddenName: me.fieldLpuSectionName,
			tabIndex: me.startTabIndex + 4,
			width: 500,
			xtype: 'swlpusectionglobalcombo',
			linkedElements: [
				me.prefix+'_EDAP_MedPersonalCombo'
			]
		}, {
			id: me.prefix+'_EDAP_MedPersonalCombo',
			hiddenName: me.fieldMedStaffFactName,
			lastQuery: '',
			listWidth: 670,
			tabIndex: me.startTabIndex + 5,
			width: 500,
			xtype: 'swmedstafffactglobalcombo',
			parentElementId: me.prefix+'_EDAP_LpuSectionCombo',
			listeners: {
				'change': function(combo, newValue, oldValue) {
					if(newValue){
						var base_form = me.getBaseForm();
						var fieldDoctorCode = base_form.findField(me.fieldDoctorCode);
						if(fieldDoctorCode.isVisible()){
							var rec = combo.findRecord('MedStaffFact_id', newValue);
							var code = rec.get('MedPersonal_DloCode');
							if(code){
								fieldDoctorCode.setValue(code);
							}else{
								fieldDoctorCode.setValue();
							}
						}
					}
				}
			}
		}, {
			id: me.prefix+'_EDAP_MedPersonalCode',
			fieldLabel: 'Код врача',
			//hidden: (getRegionNick() != 'ekb'),
			maxLength: 14,
			maskRe: /\d/,
			autoCreate: {tag: "input", size:14, maxLength: "14", autocomplete: "off"},
			name: me.fieldDoctorCode,
			//tabIndex: this.tabindex + 13,
			width: 150,
			xtype: 'numberfield'
		}, {
			border: false,
			layout: 'column',
			items: [{
				border: false,
				layout: 'form',
				items: [{
					fieldLabel: langs('№ направления'),
					name: me.fieldNumName,
					tabIndex: me.startTabIndex + 6,
					width: 150,
					xtype: 'numberfield'
				}]
			}, {
				border: false,
				labelWidth: 200,
				layout: 'form',
				items: [{
					fieldLabel: langs('Дата направления'),
					format: 'd.m.Y',
					listeners: {
						'change': function(field, newValue, oldValue) {
							blockedDateAfterPersonDeath('personpanelid', me.personPanelId, field, newValue, oldValue);
							me.getBaseForm().findField(me.fieldDiagName).setFilterByDate(newValue);
							me.checkOtherLpuDirection();
						}
					},
					name: me.fieldSetDateName,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: me.startTabIndex + 7,
					width: 100,
					xtype: 'swdatefield'
				}]
			}]
		}, {
			checkAccessRights: true,
			fieldLabel: langs('Диагноз напр. учреждения'),
			hiddenName: me.fieldDiagName,
			tabIndex: me.startTabIndex + 8,
			width: 500,
			xtype: 'swdiagcombo'
		}, {
			checkAccessRights: true,
			fieldLabel: langs('Предварительная внешняя причина'),
			hiddenName: me.fieldDiagPreidName,
			tabIndex: me.startTabIndex + 9,
			width: 500,
			xtype: 'swdiagcombo'
		}, {
			checkAccessRights: true,
			fieldLabel: langs('Предварительный диагноз'),
			hiddenName: me.fieldDiagFName,
			tabIndex: me.startTabIndex + 10,
			width: 500,
			xtype: 'swdiagcombo',
			onTabAction: function(e){
				if (e.shiftKey != true){
					e.stopEvent();
					if(this.ownerCt.prefix == 'EPLEF' && this.hiddenName == 'Diag_fid'){
						if(!me.getBaseComp().findById('EPLEF_EvnVizitPLPanel').collapsed){
							me.getBaseComp().findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
	                        me.getBaseComp().findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
						}
					} else if(this.ownerCt.prefix == 'EEPLEF' && this.hiddenName == 'Diag_fid'){
						if(!me.getBaseComp().findById('EEPLEF_EvnVizitPLPanel').collapsed && !me.getBaseComp().findById('EEPLEF_EvnVizitPLPanel').hidden){
							me.getBaseComp().findById('EEPLEF_EvnVizitPLGrid').getView().focusRow(0);
	                        me.getBaseComp().findById('EEPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
						} else if(!me.getBaseComp().findById('EEPLEF_ResultPanel').collapsed && !me.getBaseComp().findById('EEPLEF_ResultPanel').hidden) {
							me.getBaseForm().findField('EvnPL_IsFinish').focus();
						}
					} else {
						return false;
					}
				}
			}
		}];
		sw.Promed.EvnDirectionAllPanel.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swevndirectionallpanel', sw.Promed.EvnDirectionAllPanel);
// Статические методы
sw.Promed.EvnDirectionAllPanel.calcPrehospDirectId = function(Lpu_sid, Org_did, LpuSection_id, EvnDirection_IsAuto) {
	if (!EvnDirection_IsAuto) {
		EvnDirection_IsAuto = 1;
	}
	if (2 == EvnDirection_IsAuto) {
		return null;
	}
	if (!Ext.isEmpty(Lpu_sid) && LpuSection_id && Lpu_sid == getGlobalOptions().lpu_id)  {
		// Отделение ЛПУ
		if (getRegionNick() == 'kz') {
			return 8;
		} else {
			return 1;
		}
	}
	if (!Ext.isEmpty(Lpu_sid) && Lpu_sid != getGlobalOptions().lpu_id)  {
		// Другое ЛПУ
		if (getRegionNick() == 'kz') {
			return 14;
		} else {
			return 2;
		}
	}
	if (Org_did && getGlobalOptions().org_id && Org_did != getGlobalOptions().org_id && getRegionNick() != 'kz')  {
		return 3; // Другая организация
	} 
	return null;
};
sw.Promed.EvnDirectionAllPanel.isWithDirection = function(EvnDirection_id, EvnDirection_IsAuto, EvnDirection_IsReceive, EvnDirection_Num) {
	/*if (!EvnDirection_id && EvnDirection_Num) {
		// так-то тут надо возвращать значение, которое ввел пользователь, но оно не сохраняется в БД
		return 2;
	}*/
	if (!EvnDirection_id) {
		return 1;
	}
	if (!EvnDirection_IsAuto) {
		EvnDirection_IsAuto = 1;
	}
	if (!EvnDirection_IsReceive) {
		EvnDirection_IsReceive = 1;
	}
	// Нужно отображать “С ЭН”=ДА только если направление неавтоматическое или внешнее. Для остальных направлений должно быть “С ЭН”=нет
	if (1 == EvnDirection_IsAuto/* || 2 == EvnDirection_IsReceive*/) {
		return 2;
	}
	return 1;
};

sw.Promed.HtmlTemplatePanel = Ext.extend(Ext.Panel, {
	bodyStyle: 'padding: 0px',
	border: false,
	autoHeight: true,
	frame: true,
	labelAlign: 'right',
	title: null,
	data: null,
	html_tpl: null,
	win: null,
	setTemplate: function(tpl) {
		this.html_tpl = tpl;
	},
	setData: function(name, value) {
		if (!this.data) {
			this.data = new Ext.util.MixedCollection();
		}
		if (!Ext.isEmpty(name) && value != undefined) {
			var idx = this.data.findIndex('name', name);
			if (idx >= 0) {
				this.data.itemAt(idx).value = value;
			} else {
				this.data.add({
					name: name,
					value: value
				});
			}
		}
	},
	showData: function() {
		var html = this.html_tpl;
		if (this.data) {
			this.data.each(function(item) {
                var expr = new RegExp('{'+item.name+'}', 'gi');
				html = html.replace(expr, item.value);
			});
		}
		html = html.replace(/{[a-zA-Z_0-9]+}/g, '');
		this.body.update(html);
		if (this.win) {
			this.win.syncSize();
			this.win.doLayout();
		}
	},
	clearData: function() {
		this.data = null;
	}
});

sw.Promed.AttributeSignValueGridPanel = Ext.extend(sw.Promed.ViewFrame, {
	uniqueId: true,
	dataUrl: '/?c=Attribute&m=loadAttributeSignValueGrid',
	toolbar: true,
	autoLoadData: false,
	root: 'data',
	formMode: 'remote',
	denyDoubles: false,
	hideDates: false,
	isLab: null,
	requireValueText: false,
	stringfields: [
		{name: 'AttributeSignValue_id', type: 'int', header: 'ID', key: true},
		{name: 'AttributeSignValue_TablePKey', type: 'int', hidden: true},
		{name: 'AttributeSign_id', type: 'int', hidden: true},
		{name: 'AttributeSign_TableName', type: 'string', hidden: true},
		{name: 'RecordStatus_Code', type: 'int', hidden: true},
		{name: 'AttributeValueLoadParams', type: 'string', hidden: true},	//Входящие параметры для редактирования значений атрибутов
		{name: 'AttributeValueSaveParams', type: 'string', hidden: true},	//Исходящие параметры для сохранения значений атрибутов
		{name: 'AttributeSign_Code', type: 'int', header: langs('Код признака'), width: 100},
		{name: 'AttributeSign_Name', type: 'string', header: langs('Наименование признака'), id: 'autoexpand'},
		{name: 'AttributeSignValue_begDate', type: 'date', header: langs('Начало')},
		{name: 'AttributeSignValue_endDate', type: 'date', header: langs('Окончание')}
	],
	tableName: null,
	tablePKey: null,

	openEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}
		if (!this.tableName || (this.formMode == 'remote' && !this.tablePKey)) {
			return false;
		}

		var self = this;
		var grid = self.getGrid();
		var params = {
			action: action,
			requireValueText: self.requireValueText,
			formMode: self.formMode,
			requireValueText: self.requireValueText,
			formParams: {},
			callback: Ext.emptyFn
		};

		if (action == 'add') {
			params.AttributeSign_TableName = self.tableName;
			params.formParams.AttributeSignValue_TablePKey = self.tablePKey;
		} else {
			params.AttributeSign_TableName = self.tableName;

			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('AttributeSignValue_id'))) {
				return false;
			}

			params.formParams = record.data;
			if (self.formMode == 'local') {
				params.AttributeValueLoadParams = record.data.AttributeValueLoadParams;
			}
		}

		if (self.formMode == 'remote') {
			params.callback = function() {
				self.getAction('action_refresh').execute();
			};
		} else {
			params.callback = function(data) {
				if ( typeof data != 'object' || typeof data.AttributeSignValueData != 'object' ) {
					sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют необходимые данные'));
					return false;
				}
				
				var index = grid.getStore().findBy(function(rec) {
					return (
						rec.get('RecordStatus_Code') != 3  && 
						rec.get('AttributeSign_id') == data.AttributeSignValueData.AttributeSign_id && 
						rec.get('AttributeSignValue_id') != data.AttributeSignValueData.AttributeSignValue_id
					);
				});
				
				if ( index >= 0 && self.denyDoubles ) {
					sw.swMsg.alert(langs('Ошибка'), langs('Такой атрибут уже есть в списке'));
					return false;
				}

				var index = grid.getStore().findBy(function(rec) {
					return (rec.get('AttributeSignValue_id') == data.AttributeSignValueData.AttributeSignValue_id);
				});

				if ( index >= 0 ) {
					var record = grid.getStore().getAt(index);

					if ( record.get('RecordStatus_Code') == 1 ) {
						data.AttributeSignValueData.RecordStatus_Code = 2;
					}

					var grid_fields = new Array();

					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});

					for ( i = 0; i < grid_fields.length; i++ ) {
						record.set(grid_fields[i], data.AttributeSignValueData[grid_fields[i]]);
					}

					record.commit();
					grid.getStore().fireEvent('load', grid.getStore());
				}
				else {
					data.AttributeSignValueData.RecordStatus_Code = 0;

					if ( grid.getStore().getCount() == 1 && Ext.isEmpty(grid.getStore().getAt(0).get('AttributeSignValue_id')) ) {
						grid.getStore().removeAll();
					}

					data.AttributeSignValueData.AttributeSignValue_id = -swGenTempId(grid.getStore());

					var newRecord = new Ext.data.Record(data.AttributeSignValueData);
					grid.getStore().add([newRecord]);
					grid.getStore().commitChanges();
					grid.getStore().fireEvent('load', grid.getStore());
				}

				return true;
			};
		}

		if (this.hideDates) {
			params.hideDates = this.hideDates;
		}

		if (this.UslugaComplex_Code) {
			params.UslugaComplex_Code = this.UslugaComplex_Code;
		}

		/*if (this.denyDoubles) {
			var disallowAttributeSignValueCodes = [];

			params.disallowAttributeSignValueCodes = disallowAttributeSignValueCodes;
		}*/

		getWnd('swAttributeSignValueEditWindow').show(params);
		return true;
	},
	deleteSelectedRecord: function() {
		var self = this;
		var grid = self.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('AttributeSignValue_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					if (self.formMode == 'remote') {
						var params = {
							AttributeSignValue_id: record.get('AttributeSignValue_id')
						};

						Ext.Ajax.request({
							callback: function(opt, scs, response) {
								self.getAction('action_refresh').execute();
							}.createDelegate(this),
							params: params,
							url: '/?c=Attribute&m=deleteAttributeSignValue'
						});
					} else {
						switch ( Number(record.get('RecordStatus_Code')) ) {
							case 0:
								grid.getStore().remove(record);
								break;

							case 1:
							case 2:
								record.set('RecordStatus_Code', 3);
								record.commit();

								grid.getStore().filterBy(function(rec) {
									return (Number(rec.get('RecordStatus_Code')) != 3);
								});
								break;
						}

						if ( grid.getStore().getCount() > 0 ) {
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
					}
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},
	doLoad: function(params) {
		var self = this;

		if (params && params.tableName) {
			self.tableName = params.tableName;
		}
		if (params && params.tablePKey) {
			self.tablePKey = params.tablePKey;
		}

		self.loadData({
			globalFilters:{
				AttributeSign_TableName: self.tableName,
				AttributeSignValue_TablePKey: self.tablePKey,
				formMode: self.formMode
			}
		});
	},
	initComponent: function()
	{
		var self = this;
		var defaultActions = [
			{name:'action_add', handler: function(){ self.openEditWindow('add'); }},
			{name:'action_edit', handler: function(){ self.openEditWindow('edit'); }},
			{name:'action_view', handler: function(){ self.openEditWindow('view'); }},
			{name:'action_delete', handler: function(){ self.deleteSelectedRecord(); }},
			{name:'action_print', hidden: true},
			{name:'action_refresh', hidden: false}
		];

		if (self.actions) {
			var actionNames = [];
			var action = null;
			for(var i=0; i<this.actions.length; i++) {
				action = this.actions[i];
				actionNames.push(action.name);
			}
			for(var i=0; i<defaultActions; i++) {
				action = defaultActions[i];
				if (!action.name.inlist(actionNames)) {
					self.action.push(action);
				}
			}
		} else {
			self.actions = defaultActions;
		}

		sw.Promed.AttributeSignValueGridPanel.superclass.initComponent.apply(self, arguments);
	}
});

/**
 * Панель настраеваемых фильтров
 */
sw.Promed.DynamicFiltersPanel = Ext.extend(sw.Promed.Panel, {
	sysNick: '', // уникальный сис. ник. панели, к которому будут привязываться настройки
	baseFilters: [], // конфиг базовых фильтров (не перезаписываются)
	filters: [], // конфиг фильтров (перезаписываются в setFilters/loadFilters)
	doFilter: Ext.emptyFn,
	loadFilters: function(o,Tariff_begDT) {
		// загрузка фильтров с сервера
		var dynPanel = this;
		this.clearPanels();

		Ext.Ajax.request({
			url: o.url,
			params: o.params,
			scope: this,
			callback: function (o, s, r) {
				if (s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if (obj.data) {
						dynPanel.setFilters(obj.data);
						dynPanel.buildPanels(Tariff_begDT,o.params.AttributeVision_TablePKey);
					}
				}
			}
		});
	},
	setFilters: function(filters) {
		this.filters = filters;
	},
	setSysNick: function(sysNick) {
		this.sysNick = sysNick;
	},
	clearPanels: function() {
		// удалить все панели фильтрации
		this.removeAll(true);
	},
	buildPanels: function(Tariff_begDT,AttributeVision_TablePKey) {
		var dynPanel = this;
		// убираем существующие панели, если есть
		this.clearPanels();

		// todo обращаемся за настройками (локально или к бд?)

		// добавляем панели, по умолчанию 1 панель фильтрации (базовая)
		var container = new Ext.form.FieldSet({
			layout: 'form',
			collapsible: true,
			title: langs('Фильтры'),
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			defaults: {
				width: 350
			}
		});
		this.add(container);

		var columnitems = [];

		// добавляем фильтры
		this.baseFilters.forEach(function(filter) {
			columnitems.push({
				layout: 'form',
				labelWidth: 120,
				defaults: {
					width: 350
				},
				border: false,
				items: [
					filter
				]
			});
		});
		this.filters.forEach(function(filter) {
			// костыль для комбика "Профиль" не хочет грузиться и все тут
			if(filter.table && filter.table == 'LpuSectionProfile')
				filter.listeners = {render: function(c){c.getStore().load();}};
			// для комбика "Отделение", регион Вологда, тарифа «Коэффициент уровня / подуровня МО»
			if(filter.table && filter.table == 'LpuSection' && getRegionNick() == 'vologda' && AttributeVision_TablePKey==704)
				filter.listeners = {
					render: function(c){
						c.getStore().load({
							params: {object:'LpuSection',  Lpu_id: getGlobalOptions().lpu_id , LpuSection_disDate: Tariff_begDT},
						});
					}};
			filter.autoLoad = true;
			columnitems.push({
				layout: 'form',
				labelWidth: 120,
				defaults: {
					width: 350
				},
				border: false,
				items: [
					filter
				]
			});
		});

		container.add({
			border: false,
			layout: 'column',
			anchor: '-10',
			items: columnitems
		});

		container.add({
			text: BTN_FILTER,
			xtype: 'button',
			handler: function () {
				dynPanel.doFilter();
			},
			iconCls: 'search16'
		});

		container.add({
			text: BTN_RESETFILTER,
			xtype: 'button',
			handler: function () {
				dynPanel.doResetPanel();
				dynPanel.doFilter();
			},
			iconCls: 'resetsearch16'
		});

		this.doLayout();
	},
	getValues: function(panel) {
		if (panel == undefined) {
			panel = this;
		}

		var values = {};

		// очищаем все значения на панели
		if (panel.items && panel.items.items) {
			var o = panel.items.items;
			for (var i = 0, len = o.length; i < len; i++) {
				if (o[i].getValue && typeof o[i].getValue == 'function') {
					if (o[i].hiddenName) {
						values[o[i].hiddenName] = o[i].getValue();
					} else if (o[i].name) {
						values[o[i].name] = o[i].getValue();
					}
				} else if (o[i].items && o[i].items.items) {
					values = Ext.apply(values, this.getValues(o[i]));
				}
			}
		}

		return values;
	},
	doResetPanel: function(panel) {
		if (panel == undefined) {
			panel = this;
		}

		// очищаем все значения на панели
		if (panel.items && panel.items.items) {
			var o = panel.items.items;
			for (var i = 0, len = o.length; i < len; i++) {
				if (o[i].clearValue) {
					o[i].clearValue();
				} else if (o[i].setValue) {
					o[i].setValue(null);
				} else if (o[i].items && o[i].items.items) {
					this.doResetPanel(o[i]);
				}
			}
		}
	}
});

/**
* Панель с возможностью добавления комбобоксов
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @version      11.2016
*/
sw.Promed.AddOnkoComplPanel = Ext.extend(Ext.Panel,
	{
		win: null,
		firstTabIndex: null,
		loadParams: null,
		baseParams: null,
		autoHeight: true,
		bodyBorder: false,
		border: false,
		frame: false,
		header: false,
		objectName: null,
		fieldLabelTitle: null,
		labelAlign: 'right',
		labelWidth: 300,
		width: 1000,
		fieldWidth: 400,
		layout: 'form',
		bodyStyle: 'background:#DFE8F6',
		style: 'background:#DFE8F6',
		lastItemsIndex: 0,
		limitCountCombo: 26,
		buttonAlign: 'right',
		getValues: function() {
			var res = [];
			this.items.each(function(item,index,length) {
				if(item.type != 'button' && item.getValue()) {
					res.push(item.getValue());
				}
			},this);
			return res;
		},
		getSpecValues: function() {
			var pan = this;
			var res = [];
			this.items.each(function(item,index,length) {
				if(item.type != 'button') {
					var ar = {};
					ar[pan.objectName+'_id'] = item.getValue();
					res.push(ar);
				}
			},this);
			return res;
		},
		getFirstCombo: function() {
			return this.firstCombo;
		},
		reset: function(callback,values_arr) {
			this.items.each(function(item,index,length) {
				if(item.type != 'button'){
					item.hideContainer();
				}
				if(!item.name || item.name != 'addButton'){
					this.remove(item,true);
				}
			},this);
			return this.addCombo(true,callback,values_arr);
		},
		setValues: function(values_arr) {
			this.inSetValues = true;
			if(!values_arr || !Ext.isArray(values_arr))
				values_arr = [null];
			var callback = function(combo){
				if(values_arr[this.lastValueIndex] && values_arr[this.lastValueIndex][this.objectName+'_id']) {
					combo.setValue(values_arr[this.lastValueIndex][this.objectName+'_id']);
					if(this.lastValueIndex == 0){
						this.disableAddButton(false);
					}
					this.lastValueIndex++;
				}
			}.createDelegate(this);
			this.lastValueIndex = 0;
			this.loadParams = {};
			/*if(values_arr[this.lastValueIndex]) {
				this.loadParams = {this.objectName+'_id': values_arr[this.lastValueIndex]};
			}*/
			this.firstCombo = this.reset(callback,values_arr);
			for(var i=0;i<values_arr.length;i++) {
				if(i>0) {
					//this.loadParams = (values_arr[i]) ? {OnkoLateComplTreatType: values_arr[i]} : {};
					this.addCombo(false,callback,values_arr);
				}
			}
			this.inSetValues = false;
		},
		disable: function() {
			this.items.each(function(item,index,length) {
				item.disable();
			},this);		
		},
		enable: function() {
			this.items.each(function(item,index,length) {
				item.enable();
			},this);		
		},
		add: function(comp) {
	        this.initItems();
	        var a = arguments, len = a.length;
	        if(len > 1){
	            for(var i = 0; i < len; i++) {
	                this.add(a[i]);
	            }
	            return;
	        }
	        var c = this.lookupComponent(this.applyDefaults(comp));
	        var pos = this.items.length;
	        if(this.fireEvent('beforeadd', this, c, pos) !== false && this.onBeforeAdd(c) !== false){
	            this.items.add(c);
	            c.ownerCt = this;
	            this.fireEvent('add', this, c, pos);
	        }
	        return c;
	    },
		onChange: Ext.emptyFn,
		disableAddButton: function(disable){
			var items_ar = this.findBy(function(r){
				if(r.name && r.name == 'addButton'){
					return true;
				}
			});
			if(items_ar && items_ar[0]){
				var item = items_ar[0];
				item.setDisabled(disable);
			}
		},
		addCombo: function(is_first,callback,values_arr,adds) {
			var panel = this;
			if(is_first)
				this.lastItemsIndex = 0;
			else
				this.lastItemsIndex++;
			if(getRegionNick() == 'perm'){
				if(this.lastItemsIndex > this.limitCountCombo) {
					this.disableAddButton(true);
					return false;
				} else if(this.lastItemsIndex == 0){
					this.disableAddButton(true);
				} else {
					this.disableAddButton(false);
				}
			}
			var conf_combo = {
				value: null,
				labelWidth: 300,
				listeners: {
					'change': function(c,n) {
						var panel = this;
						if(getRegionNick() == 'perm' && panel.lastItemsIndex == 0 && !Ext.isEmpty(n) && n != 0){
							panel.disableAddButton(false);
						}
						if(getRegionNick() == 'perm' && !panel.inSetValues){
							panel.items.each(function(item){
								if(item.hiddenName){
									panel.loadSpr(item,panel.objectName+'_id');
								}
							});
						}
					}.createDelegate(this)
				},
				labelSeparator: '',
				hiddenName: panel.objectName+'_id'+this.lastItemsIndex,
				sortField: panel.objectName+'_Code',
				comboSubject: panel.objectName,
				autoLoad: false,
				width: panel.fieldWidth
			};
			if(this.firstTabIndex) {
				conf_combo.tabIndex = this.firstTabIndex + this.lastItemsIndex;
			}
			if(this.lastItemsIndex == 0){
				conf_combo.fieldLabel = (getRegionNick() == 'perm' ? panel.fieldLabelTitle+': 1.' : panel.fieldLabelTitle);
			} else {
				var index = this.lastItemsIndex;
				conf_combo.fieldLabel = (index+1)+'.';
			}
			var c = new sw.Promed.SwCommonSprCombo(conf_combo);
			c.findRecord = function (prop, value){
		        var record;
		        if(this.store && this.store.getCount() > 0){
		            this.store.each(function(r){
		                if(r.data[prop] == value){
		                    record = r;
		                    return false;
		                }
		            });
		        }
		        return record;
		    };
			var cb = this.add(c);
			
			this.syncSize();
			if(this.win)
				this.win.syncSize();
			
			if(adds){
				var values = panel.getSpecValues();
				panel.setValues(values);
			} else {
				this.loadSpr(cb,panel.objectName+'_id', this.loadParams,callback,values_arr);
			}
			return cb;
		},
		loadSpr: function(combo, field_value, params, callback, values_arr)
		{
			if (callback) {
				callback(combo);
			}
			var panel = this;
			var vals = [];
			var arr = panel.findBy(function(r){
				if(r.comboSubject && r.hiddenName && r.hiddenName != combo.hiddenName){
					return true;
				}
			});
			if(values_arr){
				for(var i=0;i<values_arr.length;i++){
					if(values_arr[i] && values_arr[i][panel.objectName+'_id']){
						vals.push(values_arr[i][panel.objectName+'_id']);
					}
				}
			} else {
				for(var i=0;i<arr.length;i++){
					if(arr[i].getValue()){
						vals.push(arr[i].getValue());
					}
				}
			}
			

			var value = combo.getValue();
			if(combo.store){
				combo.getStore().removeAll();
				combo.getStore().load(
				{
					callback: function() 
					{
						if (combo && typeof combo.getStore == 'function' && combo.getStore() && combo.store && combo.store.data && combo.store.data.length > 0) {
							combo.getStore().each(function (record) {
								if (record.data[field_value] == value) {
									//combo.setValue(value);
									/*combo.fireEvent('select', combo, record, 0);
									combo.fireEvent('change', combo, value, 0);*/
								} else if (record.data[field_value].inlist(vals)) {
									combo.getStore().remove(record);
								}
							});
						}
					},
					params: params 
				});
			}	
		},
		initComponent: function()
		{
			var me = this;
			var conf_add_btn = new Ext.Button({
				handler: function() {
					if(me.afterRemove){
						this.addCombo(false,false,false,true);
						me.afterRemove = false;
					} else {
						this.addCombo();
					}
					this.addButton();
				}.createDelegate(this),
				iconCls: 'add16',
				name: 'addButton',
				text: 'Добавить осложнение',
				style: 'float:right;background: #DFE8F6;',
				tabIndex: me.firstTabIndex + 11
			});
			if(getRegionNick() == 'perm'){
				this.items = [conf_add_btn];
			}
			if(typeof this.win != 'object')
				this.win = false;
			if(typeof this.loadParams != 'object')
				this.loadParams = {};
			if(typeof this.baseParams != 'object')
				this.baseParams = {level:0};

			sw.Promed.AddOnkoComplPanel.superclass.initComponent.apply(this, arguments);
		}
	}
);
Ext.reg('swaddonkocomplpanel', sw.Promed.AddOnkoComplPanel);

/**
*	Панель с информацией об общем рецепте
*/
sw.Promed.ReceptGeneralInfoPanel = Ext.extend(Ext.Panel,
{
	border: false,
	getFieldValue: function(field) {
		var result = '';
		if (this.items.items[0].getStore().getAt(0))
			result = this.items.items[0].getStore().getAt(0).get(field);
		return result;
	},
	height: 90,
	layout: 'border',
	load: function(params) {
		this.items.items[0].getStore().removeAll();
			var callback_param = (params.callback) ? params.callback : Ext.emptyFn;
			
			this.items.items[0].getStore().load({
				params: params,
				callback: callback_param
			});			
	},
	initComponent: function() {
		var me = this;
		Ext.apply(this, {
			items: [ new Ext.DataView({
				border: false,
				frame: false,
				itemSelector: 'div',
				region: 'center',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					baseParams: {
						mode: 'ReceptGeneralInfoPanel',
						
					},
					fields: [
						{name: 'EvnReceptGeneral_id'},
						{name: 'ReceptForm_Name'},
						{name: 'EvnReceptGeneral_Ser'},
						{name: 'EvnReceptGeneral_Num'},
						{name: 'EvnReceptGeneral_begDate', dateFormat: 'd.m.Y.', type: 'date'},
						{name: 'EvnReceptGeneral_endDate', dateFormat: 'd.m.Y.', type: 'date'},
						{name :'EvnReceptGeneral_Period'},
						{name: 'ReceptValid_Name'},
						{name: 'MedPersonal_FIO'},
						{name: 'Lpu_Name'},
						{name: 'Person_FIO'},
						{name: 'Person_Age'},
						{name: 'Recept_Attr'},
						{name: 'Recept_Periodicity'},
						{name: 'ReceptUrgency'}
					],
					url: '/?c=EvnRecept&m=getReceptGeneralInfo'
				}),
				style: 'padding: 1em;',
				tpl: new Ext.XTemplate(
					'<tpl for=".">'+
					'<div>Рецепт <font color = "blue">{ReceptForm_Name}</font> серия <font color = "blue">{EvnReceptGeneral_Ser}</font> № <font color = "blue">{EvnReceptGeneral_Num}</font> {EvnReceptGeneral_Period}</div>' +
					'<div>Врач <font color = "blue">{MedPersonal_FIO} / {Lpu_Name}</font></div>'+
					'<div>{Person_FIO}, возраст: {Person_Age}</div>' +
					'<div>{Recept_Attr}</div>' + 
					'<div>{Recept_Periodicity} <font color = "red">{ReceptUrgency}</font></div>' +
					'</tpl>'
				)
			})]
		});

		sw.Promed.ReceptGeneralInfoPanel.superclass.initComponent.apply(this, arguments);
	}
});

/**
*	Панель МО для ЭРС
*/
sw.Promed.ErsLpuPanel = Ext.extend(Ext.Panel, {
	autoHeight: true,
	bodyStyle: 'padding-top: 0.5em;',
	border: true,
	layout: 'form',
	style: 'margin-bottom: 0.5em;',
	title: 'Медицинская организация',
	getBaseForm: function() {
		return this.findParentByType('form').getForm();
	},
	loadLpuFSSContractCombo: function(params) {

		var base_form = this.getBaseForm();
		var lpufsscontract_combo = base_form.findField('LpuFSSContract_id');

		lpufsscontract_combo.getStore().load({
			params: {Lpu_id: base_form.findField('Lpu_id').getValue()},
			callback: function () {
				lpufsscontract_combo.fireEvent('change', lpufsscontract_combo, lpufsscontract_combo.getValue());
			}
		});
	},
	initComponent: function() {
		var me = this;
		Ext.apply(this, {
			items: [{
				layout: 'column',
				border: false,
				defaults: {
					border: false,
					style: 'margin-right: 20px;'
				},
				items: [{
					layout: 'form',
					items: [{
						xtype: 'swlpucombo',
						hiddenName: 'Lpu_id',
						disabled: true,
						width: 250,
						fieldLabel: 'Наименование МО'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 250,
						name: 'Org_INN',
						fieldLabel: 'ИНН'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 250,
						name: 'Org_OGRN',
						fieldLabel: 'ОГРН'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 250,
						name: 'Org_KPP',
						fieldLabel: 'КПП'
					}]
				}, {
					layout: 'form',
					items: [{
						codeField: 'LpuFSSContract_Num',
						displayField: 'LpuFSSContractType_Name',
						allowBlank: false,
						editable: false,
						fieldLabel: 'Договор с ФСС',
						hiddenName: 'LpuFSSContract_id',
						listWidth: 550,
						width: 300,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							url: '/?c=LpuFSSContract&m=loadList',
							fields: [
								{name: 'LpuFSSContract_id', mapping: 'LpuFSSContract_id'},
								{name: 'LpuFSSContract_Num', mapping: 'LpuFSSContract_Num'},
								{name: 'LpuFSSContractType_Name', mapping: 'LpuFSSContractType_Name'},
								{name: 'LpuFSSContract_begDate', mapping: 'LpuFSSContract_begDate'}
							],
							key: 'LpuFSSContract_id',
							sortInfo: {field: 'LpuFSSContract_Num'}
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{LpuFSSContract_Num}.</font>&nbsp;{LpuFSSContractType_Name}'+
							'</div></tpl>'
						),
						valueField: 'LpuFSSContract_id',
						listeners: {
							'change': function(combo, nv, ov) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == nv);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
							},
							'select': function (combo, record) {
								var base_form = me.getBaseForm();
								if (combo.getValue() > 0) {
									base_form.findField('LpuFSSContract_Num').setValue(record.get('LpuFSSContract_Num'));
									base_form.findField('LpuFSSContractType_Name').setValue(record.get('LpuFSSContractType_Name'));
									base_form.findField('LpuFSSContract_begDate').setValue(record.get('LpuFSSContract_begDate'));
								}
							}
						},
						xtype: 'swbaselocalcombo'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 300,
						name: 'LpuFSSContractType_Name',
						fieldLabel: 'Вид услуг по договору с ФСС'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 150,
						name: 'LpuFSSContract_Num',
						fieldLabel: 'Номер договора'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 150,
						name: 'LpuFSSContract_begDate',
						fieldLabel: 'Дата договора'
					}]
				}]
			}]
		});

		sw.Promed.ErsLpuPanel.superclass.initComponent.apply(this, arguments);
	}
});

/**
*	Панель Пациента для ЭРС
*/
sw.Promed.ErsPersonPanel = Ext.extend(Ext.Panel, {
	autoHeight: true,
	bodyStyle: 'padding-top: 0.5em;',
	border: true,
	layout: 'form',
	style: 'margin-bottom: 0.5em;',
	title: 'Получатель услуг',
	object: 'EvnERSBirthCertificate',
	getBaseForm: function() {
		return this.findParentByType('form').getForm();
	},
	checkFields: function() {

		var base_form = this.getBaseForm();

		var noPolis = !base_form.findField('Polis_Num').getValue();
		base_form.findField(this.object + '_PolisNoReason').setAllowBlank(!noPolis);
		base_form.findField(this.object + '_PolisNoReason').setDisabled(!noPolis);

		var noSnils = !base_form.findField('Person_Snils').getValue();
		base_form.findField(this.object + '_SnilsNoReason').setAllowBlank(!noSnils);
		base_form.findField(this.object + '_SnilsNoReason').setDisabled(!noSnils);

		var noDoc = !base_form.findField('DocumentType_Name').getValue();
		base_form.findField(this.object + '_DocNoReason').setAllowBlank(!noDoc);
		base_form.findField(this.object + '_DocNoReason').setDisabled(!noDoc);

		var noAddress = !base_form.findField('Address_Address').getValue();
		base_form.findField(this.object + '_AddressNoReason').setAllowBlank(!noAddress);
		base_form.findField(this.object + '_AddressNoReason').setDisabled(!noAddress);
	},
	initComponent: function() {
		var me = this;
		Ext.apply(this, {
			items: [{
				layout: 'column',
				border: false,
				defaults: {
					border: false,
					style: 'margin-right: 20px;'
				},
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						disabled: true,
						width: 250,
						name: 'Person_SurName',
						fieldLabel: 'Фамилия'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 250,
						name: 'Person_FirName',
						fieldLabel: 'Имя'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 250,
						name: 'Person_SecName',
						fieldLabel: 'Отчество'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 100,
						name: 'Person_BirthDay',
						fieldLabel: 'Дата рождения'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 250,
						name: 'Polis_Num',
						fieldLabel: 'Номер полиса'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 100,
						name: 'Polis_begDate',
						fieldLabel: 'Дата начала действия полиса'
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'ErsDataNoReason',
						hiddenName: this.object + '_PolisNoReason',
						width: 250,
						fieldLabel: 'Причина отсутствия полиса'
					}, {
						xtype: 'textfield',
						disabled: true,
						width: 250,
						name: 'Person_Snils',
						fieldLabel: 'СНИЛС'
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'ErsDataNoReason',
						hiddenName: this.object + '_SnilsNoReason',
						width: 250,
						fieldLabel: 'Причина отсутствия СНИЛС'
					}]
				}, {
					layout: 'form',
					labelWidth: 120,
					items: [{
						xtype: 'fieldset',
						autoHeight: true,
						title: 'Документ, удостоверяющий личность',
						items: [{
							xtype: 'textfield',
							disabled: true,
							width: 320,
							name: 'DocumentType_Name',
							fieldLabel: 'Вид'
						}, {
							layout: 'column',
							border: false,
							defaults: {
								border: false,
								style: 'margin-right: 20px;'
							},
							items: [{
								layout: 'form',
								items: [{
									xtype: 'textfield',
									disabled: true,
									width: 100,
									name: 'Document_Ser',
									fieldLabel: 'Серия'
								}]
							}, {
								labelWidth: 45,
								layout: 'form',
								items: [{
									xtype: 'textfield',
									disabled: true,
									width: 150,
									name: 'Document_Num',
									fieldLabel: 'Номер'
								}]
							}]
						}, {
							xtype: 'textfield',
							disabled: true,
							width: 100,
							name: 'Document_begDate',
							fieldLabel: 'Дата выдачи'
						}, {
							xtype: 'textfield',
							disabled: true,
							width: 320,
							name: 'OrgDep_Name',
							fieldLabel: 'Выдан'
						}, {
							xtype: 'swcommonsprcombo',
							comboSubject: 'ErsDataNoReason',
							hiddenName: this.object + '_DocNoReason',
							width: 320,
							fieldLabel: 'Причина отсутствия'
						}]
					}]
				}, {
					layout: 'form',
					labelWidth: 230,
					items: [{
						xtype: 'textfield',
						disabled: true,
						width: 250,
						name: 'Address_Address',
						fieldLabel: 'Место жительства'
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'ErsDataNoReason',
						hiddenName: this.object + '_AddressNoReason',
						width: 250,
						fieldLabel: 'Причина отсутствия Места жительства'
					}]
				}]
			}]
		});

		sw.Promed.ErsLpuPanel.superclass.initComponent.apply(this, arguments);
	}
});

/**
 *	Панель файлов с возможностью добавления для формы направлений (для Казахстана (БГ))
 */
sw.Promed.FileUploadPanelKZ = Ext.extend(Ext.Panel,
	{
		win: null,
		firstTabIndex: null,
		loadParams: null,
		autoHeight: true,
		bodyBorder: false,
		border: false,
		frame: false,
		header: false,
		labelAlign: 'right',
		labelWidth: 67,
		layout: 'form',
		lastItemsIndex: 0,
		limitCountCombo: 8,
		listParams: null,
		buttonAlign: 'right',
		dataUrl: null,
		saveUrl: null,
		saveChangesUrl: null,
		deleteUrl: null,
		fieldsPrefix: 'EvnMediaData',
		uploadFieldColumnWidth: .6,
		folder: null,
		getChangedData: function(){ //возвращает новые и измненные показатели
			var data = new Array();
			this.FileStore.each(function(record) {
				if (record.data.state && record.data.state.inlist(['add','delete']) ) {
					data.push(record.data);
				}
			}.createDelegate(this));
			return data;
		},
		getJSONChangedData: function(){ //возвращает новые и измненные показатели в виде закодированной JSON строки
			var dataObj = this.getChangedData();
			return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
		},
		deleteCombo: function(index) {
			var current_window = this;
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Вы хотите удалить запись?'),
				title: langs('Подтверждение'),
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						this.FileStore.findBy(function(rec) {
							if ( rec.get('Store_id') == index ) {
								if (rec.get(current_window.fieldsPrefix+'_id') == 0) {
									this.remove(rec,true);
								} else {
									rec.set('state', 'delete');
								}
							}
						});
						this.findById('Uploader'+index).hide();
						this.checkLimitCountCombo();
					}
				}.createDelegate(this)
			});
		},
		getCountCombo:  function() { //возвращает текущее количество комбо
			var cnt = 0;
			this.items.each(function(item,index,length) {
				if (!item.hidden) {
					cnt++;
				}
			});
			return cnt;
		},
		checkLimitCountCombo: function() {
			if(this.getCountCombo() >= this.limitCountCombo) {
				if (this.buttons && this.buttons[0]) {
					this.buttons[0].setDisabled(true);
				}
			} else {
				if (this.buttons && this.buttons[0] && !this.disabled) {
					this.buttons[0].setDisabled(false);
				}
			}
			if(this.win){
				this.win.syncShadow();
			}
		},
		reset: function() {
			this.items.each(function(item,index,length) {
				this.remove(item,true);
			},this);
			this.FileStore = new Ext.data.JsonStore({
				autoLoad: false,
				url: this.dataUrl,
				fields: [
					'Store_id',
					this.fieldsPrefix+'_id',
					this.fieldsPrefix+'_FilePath',
					this.fieldsPrefix+'_FileName',
					'state',
					this.fieldsPrefix+'_FileLink',
				]
			});
			this.lastItemsIndex = 0;
			this.checkLimitCountCombo();
		},
		loadData: function(params) {
			var current_window = this;
			var add_empty_combo = true; //флаг отражающий необходимость добавлять пустой комбобокс в список файлов после загрузки, если ничего не загрузилось

			if (!params || params == null)
				params = this.listParams;

			if (params.add_empty_combo != undefined) {
				add_empty_combo = params.add_empty_combo;
			}

			this.FileStore.load({
				params: params,
				callback: function() {
					current_window.FileStore.each(function(record) {
						record.data.Store_id = record.data[current_window.fieldsPrefix+'_id'];
						current_window.addCombo(true, record.data);
					});
					if (current_window.FileStore.getCount() == 0 && add_empty_combo) {
						current_window.addCombo(false);
					}
					if (params.callback)
						params.callback();
					current_window.checkLimitCountCombo();
				}
			});
		},
		saveChanges: function() {
			var current_window = this;
			var params = new Object();
			params = this.listParams;
			params.changedData = this.getJSONChangedData();

			Ext.Ajax.request({
				url: current_window.saveChangesUrl,
				callback: function(options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
					}
				},
				method: 'post',
				params: params
			});
		},
		loadFile: function(ItemsIndex) {

			var current_window = this;
			var params = this.listParams;
			var loadMask = new Ext.LoadMask(this.ownerCt.ownerCt.getEl(), {msg: LOAD_WAIT_SAVE});

			loadMask.show();

			var form = this.findById('Upload' + ItemsIndex).getForm();
			form.submit({
				url: current_window.saveUrl,
				failure: function (form, action) {
					if ( action.result ) {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
						}
					}
					loadMask.hide();
				}.createDelegate(this),
				params: params,
				success: function(form, action) {
					if (!action.result.data ) {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошла ошибка'));
						return false;
					}
					var response_obj = Ext.util.JSON.decode(action.result.data);
					form.reset();
					var ds_model = Ext.data.Record.create([
						'Store_id',
						current_window.fieldsPrefix+'_id',
						current_window.fieldsPrefix+'_FilePath',
						current_window.fieldsPrefix+'_FileName',
						'state',
						current_window.fieldsPrefix+'_FileLink'
					]);
					/*if(!current_window.FileStore)
						current_window.reset();*/
					var pos = (this.FileStore.data.first() && this.FileStore.data.first().data[this.fieldsPrefix+'_id'] != null) ? this.FileStore.data.length : 0;

					var new_ds_model = new ds_model();
					new_ds_model['Store_id'] = ItemsIndex;
					new_ds_model[current_window.fieldsPrefix+'_id'] = 0;
					new_ds_model[current_window.fieldsPrefix+'_FilePath'] = response_obj[0].file_name;
					new_ds_model[current_window.fieldsPrefix+'_FileName'] = response_obj[0].orig_name;
					new_ds_model['state'] = 'add';
					new_ds_model[current_window.fieldsPrefix+'_FileLink'] = '<a href="/uploads/'+this.folder+response_obj[0].file_name+'" target="_blank">'+response_obj[0].orig_name+'</a>';

					this.FileStore.insert(
						pos,
						new ds_model(new_ds_model)
					);
					current_window.findById('Upload' + ItemsIndex).getEl().dom.innerHTML = '<p style="margin: 7px 0 0 10px; font-size: 12px;"><a href="/uploads/'+this.folder+response_obj[0].file_name+'" target="_blank">'+response_obj[0].orig_name+'</a></p>';
					current_window.findById('Upload' + ItemsIndex).render();
					current_window.checkLimitCountCombo();
					loadMask.hide();
				}.createDelegate(this)
			});
		},
		disable: function() {
			this.items.each(function(item,index,length) {
				//item.disable();
			},this);
			var btn = this.findBy(function(obj){return obj.xtype == 'tbbutton' || obj.xtype == 'fileuploadfield' || obj.xtype == 'textfield'});
			for(var i=0; i < btn.length; i++) {
				btn[i].disable();
			}
			if (this.buttons && this.buttons[0]) {
				this.buttons[0].disable();
			}
			this.disabled = true;
		},
		enable: function() {
			/*this.items.each(function(item,index,length) {
				item.enable();
			},this);*/
			var btn = this.findBy(function(obj){return obj.xtype == 'tbbutton' || obj.xtype == 'fileuploadfield' || obj.xtype == 'textfield'});
			for(var i=0; i < btn.length; i++) {
				btn[i].enable();
			}
			if (this.buttons && this.buttons[0]) {
				this.buttons[0].enable();
			}
			this.disabled = false;
		},
		addCombo: function(is_filled, data) {
			var comp = this;
			if (is_filled) {
				var ItemsIndex = data[this.fieldsPrefix+'_id'];
				var fileup = new Ext.form.FormPanel({
					id: 'Upload' + ItemsIndex,
					bodyBorder: false,
					border: false,
					columnWidth: this.uploadFieldColumnWidth,
					html: '<p style="float: left; margin: 7px 10px 0 0; width: 100px; text-align: right; font-size: 12px;">Документ:</p><p style="float: left; margin-top: 7px; font-size: 12px;">'+data[this.fieldsPrefix+'_FileLink']+'</p>'
				});
			} else {
				this.lastItemsIndex--;
				var ItemsIndex = this.lastItemsIndex;
				var fileup = new Ext.form.FormPanel({
					id: 'Upload' + ItemsIndex,
					layout: 'form',
					fileUpload: true,
					columnWidth: this.uploadFieldColumnWidth,
					items: [{
						allowBlank: true,
						id: 'FileUpload' + ItemsIndex,
						fieldLabel: langs('Документ'),
						name: 'userfile',
						buttonText: langs('Выбрать'),
						disabled: comp.disabled,
						xtype: 'fileuploadfield',
						listeners: {
							'fileselected': function(field, value){
								this.loadFile(ItemsIndex);
							}.createDelegate(this)
						},
						width: 250
					}]
				});
			}

			var c = new Ext.Panel(
				{
					id: 'Uploader' + ItemsIndex,
					layout: 'column',
					height: 35,
					border: false,
					defaults: {
						border: false,
						bodyStyle: 'background: transparent; padding-top: 5px'
					},
					items: [fileup,
						{
							layout: 'form',
							columnWidth: .05,
							items:[{
								handler: function() {
									this.deleteCombo(ItemsIndex)
								}.createDelegate(this),
								xtype: 'tbbutton',
								disabled: comp.disabled,
								iconCls: 'delete16',
								tooltip: langs('Удалить')
							}]
						}]
				}
			);
			var cb = this.add(c);
			this.doLayout();
			this.syncSize();
			//this.buttons[0].disable();
			if(this.win)
				this.win.syncSize();
			return cb;
		},
		initComponent: function()
		{
			var cur = this;
			var conf_add_btn = {
				handler: function() {
					this.addCombo();
					this.checkLimitCountCombo();
				}.createDelegate(this),
				iconCls: 'add16',
				disabled:true,
				text: langs('Добавить файл')
			};

			if (this.buttonLeftMargin) {
				conf_add_btn.style = 'margin-left: '+ this.buttonLeftMargin +'px;';
			}
			this.FileStore = new Ext.data.JsonStore({
				autoLoad: false,
				url: this.dataUrl,
				fields: [
					'Store_id',
					this.fieldsPrefix+'_id',
					this.fieldsPrefix+'_FilePath',
					this.fieldsPrefix+'_FileName',
					'state',
					this.fieldsPrefix+'_FileLink'
				]
			});
			this.lastItemsIndex = 0;
			/*if(this.firstTabIndex) {
				conf_add_btn.tabIndex = this.firstTabIndex + 11;
			}*/
			this.buttons = [conf_add_btn];
			/*if(typeof this.win != 'object')
				this.win = false;
			if(typeof this.loadParams != 'object')
				this.loadParams = {};
			if(typeof this.baseParams != 'object')
				this.baseParams = {level:0};*/

			sw.Promed.FileUploadPanelKZ.superclass.initComponent.apply(this, arguments);
		}
	}
);
Ext.reg('swfileuploadpanelkz', sw.Promed.FileUploadPanelKZ);
