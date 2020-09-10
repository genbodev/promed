/**
* swEvnXmlViewWindow - форма просмотра Xml-документа
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @class        sw.Promed.swEvnXmlViewWindow
* @extends      sw.Promed.BaseForm
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      02.04.2014
* @comment      Префикс для id компонентов EXVW. 
*
* @input data: EvnXml_id - идентификатор Xml-документа
* @input function: onBlur - функция, вызываемая при потере фокуса окном.
* @input function: onHide - функция, вызываемая при закрытии окна.
*/

/*NO PARSE JSON*/

sw.Promed.swEvnXmlViewWindow= Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnXmlViewWindow',
	objectSrc: '/jscore/Forms/Common/swEvnXmlViewWindow.js',
	id: 'EXVW_swEvnXmlViewWindow',
	height: 450,
	width: 780,
	autoScroll: true,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: false,
	plain: false,
	resizable: false,
	collapsible: false,
	listeners:
	{
		hide: function()
		{
			this.onHide();
		},
        blur: function()
        {
            this.onBlur();
        }
	},

    winTitle: lang['forma_prosmotra_xml-dokumenta'],
	EvnXml_id: null,
	onBlur: Ext.emptyFn,
	onHide: Ext.emptyFn,
	
	show: function() 
	{
		sw.Promed.swEvnXmlViewWindow.superclass.show.apply(this, arguments);
		this.center();
		if ( !arguments[0] || !arguments[0].EvnXml_id) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		this.EvnXml_id = arguments[0].EvnXml_id;
		this.onBlur = arguments[0].onBlur || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.wTitle = arguments[0].title || null;
        this.setTitle(this.winTitle);
        this.doLoad();
		this.syncSize();
		this.doLayout();
        return true;
	},

	doLoad: function()
    {
        var thas = this;
        this.buttons[0].setDisabled(true);
		thas.buttons[1].hide();
		this.loadMask = this.getLoadMask(LOAD_WAIT);
        this.loadMask.show();
        Ext.Ajax.request({
            url: '/?c=EvnXml&m=doLoadData',
            callback: function(options, success, response) {
                thas.loadMask.hide();
                if ( !success || Ext.isEmpty(response.responseText) ) {
                    return false;
                }
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if ( response_obj.Error_Msg ) {
                    return false;
                }
                if ( !response_obj.html || !response_obj.data ) {
                    return false;
                }
				if (thas.wTitle) {
					thas.setTitle(thas.wTitle);
				} else {
					thas.setTitle(thas.winTitle +': '+ response_obj.data.EvnXml_Name);
				}

				if (response_obj.data.EvnXml_IsSigned && response_obj.data.EvnXml_IsSigned == 2) {
					thas.buttons[1].show();
				}

                thas.buttons[0].setDisabled(false);
                var tpl = new Ext.XTemplate(Ext.util.Format.htmlDecode(response_obj.html));
                tpl.overwrite(thas.evnXmlPanel.body, {});
                return true;
            },
            params: {
                EvnXml_id: this.EvnXml_id
            }
        });
	},

	initComponent: function() 
	{
		var thas = this;

        this.evnXmlPanel = new sw.Promed.Panel({
            header: false,
            bodyStyle: 'padding: 5px;'
        });

		Ext.apply(this, 
		{
			items: [
                this.evnXmlPanel
			],
			buttons: [{
                text:lang['pechat'],
                tooltip:lang['pechat_dokumenta'],
                name:'btnEvnXmlPrint',
                iconCls: 'print16',
                xtype: 'button',
                handler: function() {
					// в этой форме только просмотр
					sw.Promed.EvnXml.doPrintById(thas.EvnXml_id);
                }
            }, {
				text:langs('Версии'),
				tooltip:langs('Версии документа'),
				iconCls: 'doc-spis16',
				xtype: 'button',
				handler: function() {
					getWnd('swEMDVersionViewWindow').show({
						EMDRegistry_ObjectName: 'EvnXml',
						EMDRegistry_ObjectID: thas.EvnXml_id
					});
				}
			}, {
				text: '-'
			},
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				id: 'EXDEW_HelpButton',
				handler: function()
				{
					ShowHelp(thas.winTitle);
				}
			}, 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tabIndex: TABINDEX_ETEW + 99,
				handler: function() {
                    thas.hide();
				}
			}]
		});
		sw.Promed.swEvnXmlViewWindow.superclass.initComponent.apply(this, arguments);
	}
});