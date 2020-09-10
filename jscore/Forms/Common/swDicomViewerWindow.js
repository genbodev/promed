/**
* swDicomViewerWindow - просмотрщик Dicom файлов (префикс DVW)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      0.001-13.04.2012
* @comment		Префикс для компонентов DICOM
*/

sw.Promed.swDicomViewerWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	draggable: true,
	width: 860,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['izobrajenie_prosmotr'],
	id: 'DicomViewerWindow',
	
	setImageLink: function(link) {
		
		this.tplImage.overwrite(this.findById('DVW_ImageViewPanel').body, {
			link: link
		});
		
	},
	
	initComponent: function() {

		var that = this;
		
		var Mark = [
			'<div style="background:#FFF; width: 100%; height: 100%;"><div style="z-index: 1; position: absolute;"><img id="DICOM_Image" src="{link}"/></div><div id="DICOM_CanvasContainer" style="z-index: 2; position: relative;" /></div>'
		];
		this.tplImage = new Ext.Template(Mark);
		
		
		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'DVW_CloseButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [
				new Ext.form.FormPanel
				({
					id: 'DVW_ImagePanel',
					style : 'padding: 3px',
					autoheight: true,
					region: 'center',
					layout : 'form',
					border : false,
					frame : true,
					items: [
						{
							layout: 'column',
							items: [
								{
									layout: 'form',
									width: 120,
									items: [
										{	
											text: lang['yarkost'],
											xtype: 'label'
										},
										new Ext.Slider({
											id: 'brightnessSlider',
											width: 100,
											value: 50,
											increment: 5,
											minValue: 0,
											maxValue: 100,
											listeners: {
												change: function(slider, newValue, thumb ) {
													that.processImageFilters();
												}
											}											
										}),
										{	
											text: lang['kontrastnost'],
											style: 'margin-top: 5px;',
											xtype: 'label'
										},
										new Ext.Slider({
											id: 'contrastSlider',
											width: 100,
											value: 50,
											increment: 5,
											minValue: 0,
											maxValue: 100,
											listeners: {
												change: function(slider, newValue, thumb ) {
													that.processImageFilters();
												}
											}											
										})
									]
								},
								{
									layout: 'form',
									width: 170,
									items: [
										{	
											text: lang['rezkost'],
											xtype: 'label'
										},
										new Ext.Slider({
											id: 'sharpenSlider',
											width: 100,
											value: 0,
											increment: 1,
											minValue: 0,
											maxValue: 100,
											listeners: {
												change: function(slider, newValue, thumb ) {
													that.processImageFilters();
												}
											}
										}),
										{
											xtype: 'button',
											text: lang['invertirovat'],
											minWidth:150,
											style: 'margin-top: 11px;',
											handler: function()
											{
												that.applyImageFilter(true, true, "invert", {});
												that.processImageFilters();
											}
										}
									]
								},								
								
								{
									layout: 'form',
									width: 170,
									items: [
										{
											xtype: 'button',
											text: lang['otrazit_gorizontalno'],
											minWidth:150,
											style: 'margin-top: 10px;',
											handler: function()
											{
												that.applyImageFilter(true, true, "fliph", {});
												that.processImageFilters();
											}
										},
										{
											xtype: 'button',
											text: lang['otrazit_vetikalno'],
											minWidth:150,
											style: 'margin-top: 16px;',
											handler: function()
											{
												that.applyImageFilter(true, true, "flipv", {});
												that.processImageFilters();
											}
										}
									]
								},
								{
									layout: 'form',
									style: 'margin-top: 10px; margin-lefts: 10px;',
									width: 140,
									items: [
										{
											xtype: 'button',
											text: lang['otmenit_izmeneniya'],
											style: 'margin-top: 5px;',
											handler: function()
											{
												that.testGraf();
											}
										}
									]
								}								
							]
						},
						new Ext.form.FormPanel
						({
							id: 'DVW_ImageViewPanel',
							style : 'padding: 3px',
							height: 615,
							region: 'center',
							layout : 'form',
							border : true,
							frame : true,
							html: '<div style="background:#FFF; width: 100%; height: 100%;"><img id="DICOM_Image" src="{link}"/></div>'
						})
					]
				})
			]
		});
		sw.Promed.swDicomViewerWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('DicomViewerWindow').hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	minWidth: 600,
	inDevelopment: function() {
		sw.swMsg.alert(lang['soobschenie'], lang['funktsional_v_razrabotke']);
		return false;
	},
	// Приминение фильтра к изображению
	applyImageFilter: function(applyToImage, replaceOriginalImage, filterName, filterOptions) {
		var dicomImage = null;
		if (applyToImage) {
			dicomImage = document.getElementById('DICOM_Image');
		} else {
			dicomImage = document.getElementById('DICOM_Canvas');
		}
		
		if (dicomImage) {
				var dicomCanvas = document.getElementById('DICOM_Canvas');
				var dicomCanvasContainer = document.getElementById('DICOM_CanvasContainer');
				
				if (dicomCanvas) {
					dicomCanvasContainer.removeChild(dicomCanvas);
				}

			if (!replaceOriginalImage) {
				filterOptions.leaveDOM = true;
				
				Pixastic.process(dicomImage, filterName, filterOptions);
				
				filterOptions.resultCanvas.id = 'DICOM_Canvas';
				dicomCanvasContainer.appendChild(filterOptions.resultCanvas);
			} else {
				Pixastic.process(dicomImage, filterName, filterOptions);
			}
		} else {
			console.error("DICOM image not found!");
		}
	},
	// последовательное применение фильтров, изменяющих исходное изображение
	processImageFilters: function() {
		var filterValues = new Array();
		filterValues.push({filterName: 'brightness', filterOptions: {brightness: this.findById('brightnessSlider').value, contrast: this.findById('contrastSlider').value}});
		filterValues.push({filterName: 'sharpen', filterOptions: {amount: this.findById('sharpenSlider').value/100}});
		
		for (var i=0; i<filterValues.length; i++) {			
			this.applyImageFilter(i == 0, false, filterValues[i].filterName, filterValues[i].filterOptions);
		}
	},	
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	show: function() {
		sw.Promed.swDicomViewerWindow.superclass.show.apply(this, arguments);
		if (arguments[0] && arguments[0].link) {
			this.setImageLink(arguments[0].link);
		}
		this.restore();
		this.center();
	}
});