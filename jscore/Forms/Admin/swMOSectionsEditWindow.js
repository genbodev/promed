/**
 * swMOSectionsEditWindow - окно редактирования/добавления связи площадки МО с транспортным усзлом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @version      05.10.2011
 */

sw.Promed.swMOSectionsEditWindow = Ext.extend(sw.Promed.BaseForm,{
	action: null,
	//autoHeight: true,
	height: 600,
	buttonAlign: 'left',
	autoScroll: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	id: 'MOSectionsEditWindow',
	listeners:
	{
		hide: function()
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function()
	{
		var form = this.findById('MOSectionsForm'),
		_this = this,
		split_params = form.getForm().findField('MOSection_id').getValue().split('_'),
		params = {};
		params.type = split_params[0];

		if (params.type != 'LpuSection' && _this.action == 'edit') {
			sw.swMsg.alert(lang['oshibka'], lang['pri_redaktirovanii_vozmojno_vyibrat_tolko_otdelenie']);
			return false;
		}
		params.id = split_params[2];

		if (!Ext.isEmpty(split_params[3])) {
			params.unitType = split_params[3];
		}

		if (!Ext.isEmpty(this.deniedSectionsList)) {
			params.deniedSectionsList = _this.deniedSectionsList.join();
		} else {
			params.deniedSectionsList = null;
		}

		Ext.Ajax.request({
			callback: function(opt, scs, response) {
				if (scs) {
					var result = Ext.util.JSON.decode(response.responseText);
					var data = {
						result: result,
						type: params.type
					};
					_this.callback(data);
					_this.hide();
					_this.onHide();
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=LpuPassport&m=getMOSectionsForList'
		});

		return true;
	},
	enableEdit: function(enable) {
		var form = this.MOSectionsForm.getForm();
		this.lists = [];
		this.editFields = [];

		this.getFieldsLists(form, {
			needConstructComboLists: true,
			needConstructEditFields: true
		});

		if (enable) {
			(this.editFields).forEach(function(rec){
				rec.enable();
			});

			this.buttons[0].enable();
		} else {
			(this.editFields).forEach(function(rec){
				rec.disable();
			});
			this.buttons[0].disable();
		}
	},
	show: function(){

		sw.Promed.swMOSectionsEditWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		if (!arguments[0])
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}

		this.focus();
		this.findById('MOSectionsForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (arguments[0].LpuSection_id)
			this.LpuSection_id = arguments[0].LpuSection_id;
		else
			this.LpuSection_id = null;

		if (arguments[0].LpuBuildingPass_id)
			this.LpuBuildingPass_id = arguments[0].LpuBuildingPass_id;
		else
			this.LpuBuildingPass_id = null;

		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;
		else
			this.Lpu_id = null;

		if (arguments[0].callback)
		{
			this.callback = arguments[0].callback;
		}
		if (arguments[0].SectionsOnly)
		{
			this.SectionsOnly = arguments[0].SectionsOnly;
		} else {
			this.SectionsOnly = null;
		}
		if (arguments[0].owner)
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].action)
		{
			this.action = arguments[0].action;
		} else {
			if ( ( this.LpuSection_id ) && ( this.LpuSection_id > 0 ) )
				this.action = "edit";
			else
				this.action = "add";
		}

		if (!Ext.isEmpty(arguments[0].deniedSectionsList)) {
			this.deniedSectionsList = arguments[0].deniedSectionsList;
		} else {
			this.deniedSectionsList = null;
		}

		if ( arguments[0] )
		{
			if ( arguments[0].Lpu_id ){
				this.findById('lpu-structure-frame-sections').Lpu_id = arguments[0].Lpu_id;
			}
			else{
				this.findById('lpu-structure-frame-sections').Lpu_id = getGlobalOptions().lpu_id;
			}
			if(arguments[0].action) this.action = arguments[0].action;
			else this.action = 'edit';
		} else {
			this.findById('lpu-structure-frame-sections').Lpu_id = getGlobalOptions().lpu_id;
			this.action = "edit";
		}

		var form = this.findById('MOSectionsForm');

		this.findById('lpu-structure-frame-sections').getLoader().load(this.findById('lpu-structure-frame-sections').getRootNode());

		switch (this.action) {
			case 'add':
				this.setTitle(lang['svyaz_zdaniya_mo_s_otdeleniyami_dobavlenie']);
				this.enableEdit(true);
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['svyaz_zdaniya_mo_s_otdeleniyami_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['svyaz_zdaniya_mo_s_otdeleniyami_prosmotr']);
				this.enableEdit(false);
				break;
		}
	},
	TreeBeforeLoad: function(TreeLoader, node){
		var panel = Ext.getCmp('lpu-structure-frame-sections'),
			_this = this;
		TreeLoader.baseParams.level = node.getDepth();
		//TreeLoader.baseParams.level_two = 'All';
		TreeLoader.baseParams.level_two = 'LpuSection';
		if (node.getDepth()==0) {
			TreeLoader.baseParams.object = 'Lpu';
		} else {
			TreeLoader.baseParams.object = node.attributes.object;
			TreeLoader.baseParams.object_id = node.attributes.object_value;
		}
		if (!panel.Lpu_id) {
			//запрещаем загрузку при инициализации
			return false;
			//TreeLoader.baseParams.Lpu_id = 0;
		} else {
			TreeLoader.baseParams.Lpu_id = panel.Lpu_id;
		}

		if (node.attributes.object=='LpuUnitType')
			TreeLoader.baseParams.LpuUnitType_id = node.attributes.LpuUnitType_id;
		else
			TreeLoader.baseParams.LpuUnitType_id = 0;

		if (node.attributes.object_key)
			TreeLoader.baseParams.object_key = node.attributes.object_key;

		TreeLoader.baseParams.SectionsOnly = true;

		if (!Ext.isEmpty(_this.deniedSectionsList)) {
			TreeLoader.baseParams.deniedSectionsList = _this.deniedSectionsList.join();
		}

		TreeLoader.baseParams.LpuBuildingPass_id = _this.LpuBuildingPass_id;

		return true;
	},
	LpuStructureTreeClick: function(node,e) {
		log(node.id);
		this.MOSectionsForm.getForm().findField('MOSection_id').setValue(node.id);
	},
	initComponent: function() {
		var _this = this;

		_this.swLpuStructureFrame = new sw.Promed.LpuStructure({id:'lpu-structure-frame-sections'});
		_this.swLpuStructureFrame.loader.on("beforeload", function(TreeLoader, node) {return this.TreeBeforeLoad(TreeLoader, node);}.createDelegate(this), this);
		_this.swLpuStructureFrame.on('click', function(node, e) {_this.LpuStructureTreeClick(node, e)} );
		_this.swLpuStructureFrame.width = 570;
		_this.swLpuStructureFrame.loader.addListener('load', function (loader,node){
			if (node==_this.swLpuStructureFrame.root)
			{
				if (_this.swLpuStructureFrame.rootVisible == false)
				{
					if (node.hasChildNodes() == true)
					{
						node = node.findChild('object', 'Lpu');
						_this.swLpuStructureFrame.fireEvent('click', node);
					}
				}
			}
			node.eachChild(function(child){
				var ui = child.getUI();
				if (!Ext.isEmpty(child.attributes.claimed) && child.attributes.claimed == 1) {
					ui.addClass('x-tree-section-claimed');
				}
			});
		});

		new Ext.tree.TreeSorter(_this.swLpuStructureFrame, {
			folderSort: false,
			sortType: function(node) {
				var text = node.attributes.text;
				if(node.attributes.object == 'MedService')
					text = 'яяя'+text;
				if(node.attributes.object == 'Storage')
					text = 'яяю'+text;
				if(node.attributes.object == 'LpuRegionTitle')
					text = 'яящ'+text;
				return text;
			}
			//,property: 'order'
		});

		this.MOSectionsForm = new Ext.form.FormPanel(
			{
				autoHeight: true,
				bodyStyle: 'padding: 5px',
				border: false,
				buttonAlign: 'left',
				//autoScroll: true,
				frame: true,
				id: 'MOSectionsForm',
				labelAlign: 'right',
				labelWidth: 180,
				items:[{
					name: 'MOSection_id',
					id: 'MOSEW_MOSection_id',
					xtype: 'hidden'
				},{
					layout: 'form',
					bodyStyle:'background-color: #ffffff',
					items: [
						_this.swLpuStructureFrame
					]
				}]
			});

		Ext.apply(this,{
			buttons:[{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_LPEEW + 16,
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_LPEEW + 17,
				text: BTN_FRMCANCEL
			}],
			xtype: 'panel',
			items: [_this.MOSectionsForm]
		});
		sw.Promed.swMOSectionsEditWindow.superclass.initComponent.apply(this, arguments);
	}
});