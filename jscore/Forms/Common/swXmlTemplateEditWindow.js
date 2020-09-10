/**
* swXmlTemplateEditWindow - форма редактирования шаблонов
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @class        sw.Promed.swXmlTemplateEditWindow
* @extends      sw.Promed.BaseForm
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      20.05.2013
* @comment      Префикс для id компонентов XTEW. 
*
 * @input string action - действие (add, copy, edit, view)
 * @input integer LpuSection_id - идентификатор отделения текущего пользователя
 * @input integer Lpu_id - идентификатор ЛПУ текущего пользователя
 * @input integer formParams.XmlTemplate_id - идентификатор шаблона
 * @input integer formParams.EvnClass_id - идентификатор категории шаблона
 * @input integer formParams.XmlTemplateType_id - идентификатор типа шаблона
 * @input integer formParams.XmlType_id - идентификатор типа документа
 * @input integer formParams.XmlTypeKind_id - идентификатор вида документа
 * @input integer formParams.XmlTemplateCat_id - идентификатор папки
 * @input integer formParams.UslugaComplex_id - идентификатор услуги
 * @input function callback - функция, вызываемая при успешном сохранении шаблона, получает response в параметрах.
*/

/*NO PARSE JSON*/

sw.Promed.swXmlTemplateEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swXmlTemplateEditWindow',
	objectSrc: '/jscore/Forms/Common/swXmlTemplateEditWindow.js',
	maximizable: true,
	id: 'XTEW',
	height: 570,
	autoScroll: true,
	width: 700,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: false,
	plain: false,
	resizable: false,
	collapsible: false,
	title: lang['shablon_redaktirovanie'],
	action: null,
	listeners: { 
		'maximize': function(win) {
			var new_height = win.getInnerHeight() - 503 + 295;
			win.findById('XTEW_XmlTemplate_HtmlTemplate').onResize('100%',new_height);
		},
		'restore': function(win) {
			// win.getInnerHeight() 503
			win.findById('XTEW_XmlTemplate_HtmlTemplate').onResize('100%',295);
		}
	},

	initComponent: function() 
	{
		var thas = this;
		Ext.apply(this, 
		{
			//layout: 'border',
			items: [
				new Ext.form.FormPanel({
					//region: 'center',
					animCollapse: false,
					autoHeight: true,
					bodyStyle: 'padding: 2px 2px 0; height: 100%',
					border: false,
					buttonAlign: 'left',
					frame: true,
					labelAlign: 'right',
					labelWidth: 140,
					//title: 'Параметры шаблона',
					id: 'XTEW_XmlTemplateEditForm',
					reader: new Ext.data.JsonReader({
						success: Ext.emptyFn
					}, [
						{name: 'XmlTemplate_id'},
                        {name: 'Lpu_id'},
                        {name: 'LpuSection_id'},
                        {name: 'XmlTemplateScope_id'},
                        {name: 'XmlTemplateScope_eid'},
                        {name: 'XmlTemplateType_id'},
                        {name: 'XmlType_id'},
                        {name: 'XmlTypeKind_id'},
                        {name: 'EvnClass_id'},
                        {name: 'XmlTemplateCat_id'},
                        {name: 'UslugaComplex_id'},
                        {name: 'XmlTemplate_HtmlTemplate'}
					]),
					url: '/?c=XmlTemplate&m=save',
					items: 
					[{
                        name: 'XmlTemplate_id',
                        xtype: 'hidden'
                    }, {
                        name: 'Lpu_id',
                        xtype: 'hidden'
                    }, {
                        name: 'LpuSection_id',
                        xtype: 'hidden'
                    }, {
                        name: 'XmlTemplateScope_id',
                        xtype: 'hidden'
                    }, {
                        name: 'XmlTemplateScope_eid',
                        xtype: 'hidden'
                    }, {
                        name: 'XmlTemplateType_id',
                        xtype: 'hidden'
                    }, {
                        name: 'XmlType_id',
                        xtype: 'hidden'
                    }, {
                        name: 'XmlTypeKind_id',
                        xtype: 'hidden'
                    }, {
                        name: 'EvnClass_id',
                        xtype: 'hidden'
                    }, {
                        name: 'XmlTemplateCat_id',
                        xtype: 'hidden'
                    }, {
                        name: 'UslugaComplex_id',
                        xtype: 'hidden'
                    }, {
						allowBlank: false,
						hideLabel: true,
						name: 'XmlTemplate_HtmlTemplate',
						id: 'XTEW_XmlTemplate_HtmlTemplate',
						height: 600,
						CKConfig: {
							toolbarStartupExpanded: true,
							customConfig : '/ckeditor/config.js',
							toolbar: 'designer'
						},
						xtype: 'ckeditor'
                    }]
				})
			],
			buttons: [{
				iconCls: 'save16',
				text: BTN_FRMSAVE,
				tooltip: lang['sohranit'],
				handler: function() {
					thas.doSave();
				},
				tabIndex: TABINDEX_ETEW + 48
			},{
				iconCls: 'save16',
				text: lang['sohranit_kak'],
				tooltip: lang['sohranit_kak'],
				handler: function() {
					thas.doCopy();
				},
				tabIndex: TABINDEX_ETEW + 49
			},{
				text: '-'
			},
			/*{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				id: 'ETEW_HelpButton',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			},*/
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tabIndex: TABINDEX_ETEW + 50,
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			enableKeyEvents: true,
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					if (e.getKey() == Ext.EventObject.ESC)
					{
						Ext.getCmp('XTEW').hide();
						return false;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swXmlTemplateEditWindow.superclass.initComponent.apply(this, arguments);
    },
    setDefaultValues: function(base_form)
    {
        base_form.findField('XmlTemplateType_id').setValue(6);
        base_form.findField('LpuSection_id').setValue(this.LpuSection_id);
        base_form.findField('Lpu_id').setValue(this.Lpu_id);
        base_form.findField('XmlTemplateScope_id').setValue(sw.Promed.XmlTemplateScopePanel.getDefaultXmlTemplateScopeId('XmlTemplate', this.Lpu_id, this.LpuSection_id));
        base_form.findField('XmlTemplateScope_eid').setValue(sw.Promed.XmlTemplateScopePanel.getDefaultXmlTemplateScopeEid('XmlTemplate', this.Lpu_id, this.LpuSection_id));
    },
    show: function()
    {
		sw.Promed.swXmlTemplateEditWindow.superclass.show.apply(this, arguments);
		this.center();
		var thas = this;
		var base_form = this.findById('XTEW_XmlTemplateEditForm').getForm();
		base_form.reset();

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

        if(arguments[0].formParams)
            base_form.setValues(arguments[0].formParams);

        this.action = arguments[0].action || null;
        this.LpuSection_id = arguments[0].LpuSection_id || (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.LpuSection_id);
        this.Lpu_id = arguments[0].Lpu_id || (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.Lpu_id);
        this.callback = arguments[0].callback || Ext.emptyFn;
        this.disabledChangeEvnClass = arguments[0].disabledChangeEvnClass || false;
        this.disabledChangeXmlType = arguments[0].disabledChangeXmlType || false;
		
		var editor = base_form.findField('XmlTemplate_HtmlTemplate').getCKEditor();
		if(editor) {
			if (editor.setFDMEvnClass)
				editor.setFDMEvnClass(base_form.findField('EvnClass_id').getValue());
			else
				editor.config.EvnClass_id = base_form.findField('EvnClass_id').getValue();
		}

		if ( ! this.action )
		{
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuet_parametr_action'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

        switch ( this.action ) {
            case 'add':
                this.setTitle(lang['shablon_dobavlenie']);
                this.buttons[0].enable();
                this.buttons[1].disable();
                this.setDefaultValues(base_form);
                editor.setData('<div class="printonly" style="display: block;">' +
                    lang['verhnyaya_chast_dokumenta'] +
                    '</div>' +
                    lang['mesto_dlya_razdelov_dokumenta'] +
                    '<div class="printonly" style="display: block;">' +
                    lang['nijnyaya_chast_dokumenta'] +
                    '</div>');
                break;
            case 'copy':
            case 'view':
            case 'edit':
                if ('copy' == this.action) {
                    this.setTitle(lang['shablon_kopirovanie']);
                    this.buttons[0].enable();
                    this.buttons[1].disable();
                } else if ('edit' == this.action) {
                    this.setTitle(lang['shablon_redaktirovanie']);
                    this.buttons[0].enable();
                    this.buttons[1].enable();
                } else {
                    this.setTitle(lang['shablon_prosmotr']);
                    this.buttons[0].disable();
                    this.buttons[1].enable();
                }
                var Mask = new Ext.LoadMask(Ext.get('XTEW'), {msg: "Пожалуйста, подождите, идет загрузка данных формы..."} );
                Mask.show();
                base_form.load({
                    failure: function(form, action) {
                        Mask.hide();
                        var msg = lang['ne_udalos_zagruzit_dannyie_s_servera'];
                        var result = Ext.util.JSON.decode(action.response.responseText);
                        if (result && result.Error_Msg) {
                            msg = result.Error_Msg;
                        }
                        sw.swMsg.alert(lang['oshibka'], msg, function() {Mask.hide();thas.hide();} );
                    },
                    params: {
                        XmlTemplate_id: base_form.findField('XmlTemplate_id').getValue()
                    },
                    success: function() {
                        Mask.hide();
                        // Что-то делаем в зависимости от XmlTemplateType_id
                        var XmlTemplateType_id = base_form.findField('XmlTemplateType_id').getValue();

                    },
                    url: '/?c=XmlTemplate&m=loadForm'
                });
                break;
            default:
                thas.hide();
                return false;
                break;
        }
        this.maximize();
        return true;
	},
	/** Сохранить как */
	doCopy: function() {
		var base_form = this.findById('XTEW_XmlTemplateEditForm').getForm();
		var params = {};
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById('XTEW_XmlTemplateEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.setDefaultValues(base_form);
		var lpusection_name = lang['neizvestno'];
		if (this.LpuSection_id == getGlobalOptions().CurLpuSection_id) {
			lpusection_name == getGlobalOptions().CurLpuSection_Name;
		} else if (sw.Promed.MedStaffFactByUser.last && this.LpuSection_id == sw.Promed.MedStaffFactByUser.last.LpuSection_id) {
			lpusection_name == sw.Promed.MedStaffFactByUser.last.LpuSection_Name;
		}

		// открываем форму ввода настроек для замены настроек
		getWnd('swXmlTemplateSettingsEditWindow').show({
			disabledChangeEvnClass: true, // нельзя менять из-за спемаркеров
			disabledChangeXmlType: this.disabledChangeXmlType,
			action: 'add',
			withoutSave: true,
			XmlTemplate_id: base_form.findField('XmlTemplate_id').getValue(),// загружаем настройки этого шаблона, а потом заменяю их на overrideData
			overrideData: {
				// новые значения по умолчанию были установлены в setDefaultValues
				XmlTemplateScope_id: base_form.findField('XmlTemplateScope_id').getValue(),
				XmlTemplateScope_eid: base_form.findField('XmlTemplateScope_eid').getValue(),
				Lpu_id: base_form.findField('Lpu_id').getValue(),// == this.Lpu_id == getGlobalOptions().lpu_id || sw.Promed.MedStaffFactByUser.last.Lpu_id
				Lpu_Name: getGlobalOptions().lpu_nick || getGlobalOptions().org_nick, // sw.Promed.MedStaffFactByUser.last.Lpu_Nick sw.Promed.MedStaffFactByUser.last.Org_Nick
				LpuSection_id: base_form.findField('LpuSection_id').getValue(),// == this.LpuSection_id == getGlobalOptions().CurLpuSection_id || sw.Promed.MedStaffFactByUser.last.LpuSection_id
				LpuSection_Name: lpusection_name,
				PMUser_Name: getGlobalOptions().pmuser_name
			},
			callback: function(formParams) {
				// устанавливаем то, что мог изменить пользователь
				base_form.findField('XmlTemplateScope_id').setValue(formParams.XmlTemplateScope_id);
				base_form.findField('XmlTemplateScope_eid').setValue(formParams.XmlTemplateScope_eid);
				base_form.findField('XmlTemplateCat_id').setValue(formParams.XmlTemplateCat_id);
				base_form.findField('XmlType_id').setValue(formParams.XmlType_id);
				base_form.findField('XmlTypeKind_id').setValue(formParams.XmlTypeKind_id);
				// настройки печати и поля, которых нет на этой форме, передаем в params
				params.XmlTemplate_Caption = formParams.XmlTemplate_Caption;
				params.UslugaComplex_id_list = formParams.UslugaComplex_id_list;
				params.PaperFormat_id = formParams.PaperFormat_id;
				params.PaperOrient_id = formParams.PaperOrient_id;
				params.FontSize_id = formParams.FontSize_id;
				params.margin_left = formParams.margin_left;
				params.margin_top = formParams.margin_top;
				params.margin_bottom = formParams.margin_bottom;
				params.margin_right = formParams.margin_right;
				// сохраняем и шаблон и настройки
				base_form.url = '/?c=XmlTemplate&m=copy';
				var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение шаблона..."});
				loadMask.show();
				base_form.submit({
					failure: function(result_form, action) {
						loadMask.hide();
						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
							}
						}
					}.createDelegate(this),
					params: params,
					success: function(result_form, action) {
						loadMask.hide();
						var result = Ext.util.JSON.decode(action.response.responseText);
						if ( result && result['XmlTemplate_id'] && result['success'] && result['success'] === true )
						{
							if(getWnd('swTemplSearchWindow').isVisible()) {
								getWnd('swTemplSearchWindow').doLoadData(true);
							}
							base_form.reset();
							this.hide();
							this.callback(result);
							if (formParams.callback) formParams.callback();
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			}.createDelegate(this)
		});
		return true;
	},
	doSave: function(options) {
		if ('copy' == this.action) {
			return this.doCopy();
		} 
		var base_form = this.findById('XTEW_XmlTemplateEditForm').getForm();
		var params = {};
        if ('edit' == this.action) {
            // для проверки доступа, в БД не пишется
            var LpuSection_uid = this.LpuSection_id;
            if (!LpuSection_uid) {
                LpuSection_uid = getGlobalOptions().CurLpuSection_id || null;
            }
            base_form.findField('LpuSection_id').setValue(LpuSection_uid);
        }

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById('XTEW_XmlTemplateEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		base_form.url = '/?c=XmlTemplate&m=save';
        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение шаблона..."});
        loadMask.show();
        base_form.submit({
            failure: function(result_form, action) {
                loadMask.hide();
                if ( action.result ) {
                    if ( action.result.Error_Msg ) {
                        sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                    }
                    else {
                        sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
                    }
                }
            }.createDelegate(this),
            params: params,
            success: function(result_form, action) {
                loadMask.hide();
                var result = Ext.util.JSON.decode(action.response.responseText);
                if ( result && result['XmlTemplate_id'] && result['success'] && result['success'] === true )
                {
                    base_form.reset();
                    this.hide();
                    getWnd('swXmlTemplateSettingsEditWindow').show({
                        XmlTemplate_id: result['XmlTemplate_id'],
						action: this.action,
                        disabledChangeEvnClass: this.disabledChangeEvnClass,
                        disabledChangeXmlType: this.disabledChangeXmlType,
                        callback: function() {
                            this.callback(result);
                        }.createDelegate(this)
                    });
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                }
            }.createDelegate(this)
        });
		return true;
	}
});