/**
* swEmkDocumentViewWindow - окно просмотра документа ЭМК.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      23.10.2012
*/

sw.Promed.swEmkDocumentViewWindow = Ext.extend(sw.Promed.BaseForm, 
{
	width : 700,
	height : 500,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	autoScroll: true,
	border : false,
	plain : true,
	action: null,
	maximized: true,
	title: lang['prosmotr_dokumenta'],
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	loadNodeViewForm: function() 
	{
		this.rightPanel.loadNodeViewForm(this.viewObject, this.dopParam);
	},
	onSelect: function() 
	{
		var data = {
			wholeDoc: this.rightPanel.body.dom.innerHTML
		};
		try {
			document.execCommand('copy',false,null);
			data.isExecCommandCopy = true;
		} catch(err) {
			data.isExecCommandCopy = false;
			var s = (window.getSelection) ? window.getSelection() : document.selection;
			if(s && s.rangeCount > 0) {
				data.range = s.getRangeAt(0);
			}
		}
		this.callback(data);
		this.hide();
	},

	show: function() 
	{
		sw.Promed.swEmkDocumentViewWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] || !arguments[0].objectCode ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		var s = (window.getSelection) ? window.getSelection() : document.selection;
		s.removeAllRanges();
		/*
		this.parentObjectCode = arguments[0].parentObjectCode;
		this.objectName = ;
		this.objectId = arguments[0].objectId;
		this.objectKey = arguments[0].objectKey;
		*/
		this.userMedStaffFact = arguments[0].userMedStaffFact || sw.Promed.MedStaffFactByUser.last;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.viewObject = {
			id: arguments[0].objectCode +'_'+ arguments[0].objectId	,
			attributes: {
				accessType: 'view',
				text: 'test',
				object: arguments[0].objectCode,
				object_id: arguments[0].objectKey,
				object_value: arguments[0].objectId			
			},
			parentNode: {
				attributes: {
					accessType: 'view',
					text: 'test',
					object: arguments[0].parentObjectCode,
					object_id: arguments[0].parentKey,
					object_value: arguments[0].parentId			
				}
			}
		};
		this.dopParam = {
			param_name: arguments[0].parentKey,
			param_value: arguments[0].parentId
		};
		this.loadNodeViewForm();
		
		
	},
	initComponent: function() 
	{

		this.rightPanel = new Ext.Panel(
		{
			animCollapse: false,
			autoScroll: true,
			bodyStyle: 'background-color: #e3e3e3',
			floatable: false,			
			minSize: 400,
			region: 'center',
			id: 'rightEmkPanel',
			split: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false,
				style: 'border 0px'
			},
			items: 
			[{
				html: ''
			}]
		});
		
		Ext.apply(this.rightPanel,sw.Promed.viewHtmlForm);
		this.rightPanel.ownerWindow = this;
		var win = this;
		this.rightPanel.configActions = {
		};
		
		Ext.apply(this, 
		{
			region: 'center',
			layout: 'border',
			buttons: 
			[/*{
				handler: function() {
					win.onSelect();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, */{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					win.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: {
				autoScroll: true,
				bodyBorder: false,
				frame: false,
				xtype: 'form',
				region: 'center',
				layout: 'border',
				border: false,
				items: [this.rightPanel]
			}
		});
		sw.Promed.swEmkDocumentViewWindow.superclass.initComponent.apply(this, arguments);
	}
});