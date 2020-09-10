/**
* swEvnXmlEditWindow - форма добавления/просмотра/редактирования Xml-документа
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @class        sw.Promed.swEvnXmlEditWindow
* @extends      sw.Promed.BaseForm
* @copyright    Copyright (c) 2009-2015 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      05.2015
*
* @input data: EvnXml_id - идентификатор Xml-документа
* @input function: onBlur - функция, вызываемая при потере фокуса окном.
* @input function: onHide - функция, вызываемая при закрытии окна.
*/

/*NO PARSE JSON*/

sw.Promed.swEvnXmlEditWindow= Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnXmlEditWindow',
	objectSrc: '/jscore/Forms/Common/swEvnXmlEditWindow.js',
	id: 'swEvnXmlEditWindow',
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
			this.onHide(this);
		},
        blur: function()
        {
            this.onBlur();
        }
	},
    winTitle: lang['forma_xml-dokumenta'],
	
	show: function() 
	{
        var me = this;
		sw.Promed.swEvnXmlEditWindow.superclass.show.apply(this, arguments);
		if ( !arguments[0] || !arguments[0].action || (!arguments[0].userMedStaffFact && !arguments[0].MedService_id)
            || !arguments[0].Evn_id || !arguments[0].EvnClass_id
        ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { me.hide(); } );
			return false;
		}
        me.center();
        me.action = arguments[0].action;
        me.XmlType_id = arguments[0].XmlType_id;
        me.EvnXml_id = arguments[0].EvnXml_id;
        me.onBlur = arguments[0].onBlur || Ext.emptyFn;
        me.onHide = arguments[0].onHide || Ext.emptyFn;
        me.evnXmlPanel.doReset();
        me.evnXmlPanel.options = {
            EvnClass_id: arguments[0].EvnClass_id,
            XmlType_id: arguments[0].XmlType_id || null
        };
        me.setTitle(arguments[0].title || me.winTitle);
        me.evnXmlPanel.setReadOnly(me.action == 'view');
        me.evnXmlPanel.setBaseParams({
            userMedStaffFact: arguments[0].userMedStaffFact,
            //Server_id: arguments[0].Server_id, // getGlobalOptions().Server_id
            UslugaComplex_id: arguments[0].UslugaComplex_id || null,
            Evn_id: arguments[0].Evn_id
        });
        if (arguments[0].EvnXml_id) {
            me.evnXmlPanel.doLoadData({
                EvnXml_id: arguments[0].EvnXml_id,
                Evn_id: arguments[0].Evn_id
            });
        } else if (arguments[0].XmlType_id) {
			if (me.XmlType_id.inlist([4,7,17]) && me.action.inlist(['add','edit'])) {
				me.evnXmlPanel.doSelectXmlTemplate();
			} else {
				me.evnXmlPanel.loadXmlTemplateDefault(function(id){
					if (id) {
						me.evnXmlPanel._createEmpty(id);
					} else {
						sw.swMsg.alert(lang['soobschenie'], lang['shablon_po_umolchaniyu_ne_ustanovlen'], function() { } );
					}
				});
			}
        }
        return true;
	},
	initComponent: function()
	{
		var me = this;

        me.evnXmlPanel = new sw.Promed.EvnXmlPanel({
            autoHeight: true,
            header: false,
            bodyStyle: 'padding: 5px;',
            border: false,
            collapsible: true,
            layout: 'form',
            isLoaded: false,
            ownerWin: this,
			signEnabled: true,
            options: {
                XmlType_id: sw.Promed.EvnXml.EVN_VIZIT_PROTOCOL_TYPE_ID, // только протоколы осмотра
                EvnClass_id: 11 // документы и шаблоны только категории посещение поликлиники
            }
        });

		Ext.apply(me,
		{
			items: [
                me.evnXmlPanel
			],
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tabIndex: TABINDEX_ETEW + 99,
				handler: function() {
                    me.hide();
				}
			}]
		});
		sw.Promed.swEvnXmlEditWindow.superclass.initComponent.apply(me, arguments);
	}
});