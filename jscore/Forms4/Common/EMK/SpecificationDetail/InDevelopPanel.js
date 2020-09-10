/**
 * InDevelopPanel - Добавление лекарственного назначения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.SpecificationDetail.InDevelopPanel', {
	/* свойства */
	emptyPanel: true,
	alias: 'widget.InDevelopPanel',
	autoShow: false,
	cls: 'InDevelopPanel',
	constrain: true,
	extend: 'Ext6.panel.Panel',
	header: false,
	border: false,
	scrollable: true,
	title: '',
	width: '100%',
	autoHeight: true,
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	typePanel: 'SpecificationPanel',
	manyDrug: false,
	data: {},
	parentPanel: {},
	/* методы */
	show: function(data) {
		this.callParent(arguments);

		var me = this;
		if(data){
			me.data = data;
			me.callback = (typeof data.callback == 'function' ? data.callback : Ext6.emptyFn);
			me.formParams = (typeof data.formParams == 'object' ? data.formParams : {});
			if(data.mode){
				me.setMode(data.mode);
			}
		}

	},
	setMode: function(mode){
		var html = '';
		// @todo сделать все в Ext6.Template, а не вот это вот всё
		switch(mode){
			case 'PacketWindow':
				html = '<div class="specific-action-container" style="width: 270px;">' +
					'<p class="action-container-text">Выберите шаблон из списка или создайте новый, кликнув на иконку с плюсом справа от данной надписи</p>' +
					'<div class="action-container-img PacketWindow"></div>' +
					'</div>';
				break;
			case 'SpecificationWindow':
				html = '<div class="specific-action-container" style="width: 270px;">' +
					'<p class="action-container-text">Выберите назначение из списка или добавьте назначение, кликнув на плюс справа от заголовка группы назначений</p>' +
					'<div class="action-container-img SpecificationWindow"></div>' +
					'</div>';
				break;
			default:
				html = '<div class="specific-action-container" style="width: 270px;">' +
					'<p class="action-container-text">Выберите назначение из списка или добавьте назначение, кликнув на плюс справа от заголовка группы назначений</p>' +
					'<div class="action-container-img"></div>' +
					'</div>';
		}
		this.Message.setHtml(html);
	},
	/* конструктор */
	initComponent: function() {
		var win = this;

		this.Message = Ext6.create('Ext6.panel.Panel',{
			border:false,
			frame: false,
			html: '<div class="specific-action-container" style="width: 270px;">' +
			'<p class="action-container-text">Выберите назначение из списка или добавьте назначение, кликнув на плюс справа от заголовка группы назначений</p>' +
			'<div class="action-container-img"></div>' +
			'</div>'
		});

		Ext6.apply(win, {
			items: [{
				border: false,
				layout: 'center',
				scrollable: true,
				anchor: 'center',
				width: '75%',
				height: '95%',
				bodyPadding: '0',
				items: [win.Message]
			}]
		});

		this.setMode(this.mode);

		this.callParent(arguments);
	}
});
