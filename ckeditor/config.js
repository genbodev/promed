/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.config.html
*/

CKEDITOR.editorConfig = function( config )
{
	config.skin = 'office2003'; // or 'default' or 'office2003' or 'v2'
	config.resize_enabled = false;
	config.height = '25em';
	config.width = '100%';
	config.removePlugins = 'elementspath,save,templates,scayt,forms,pagebreak,smiley,specialchar,about,a11yhelp,find,flash,pastetext,wsc,tab,print,newpage,filebrowser';
	//tabletools,table,  
	config.pasteFromWordNumberedHeadingToList = true;
	config.pasteFromWordRemoveFontStyles = true;
	/*
	config.blockedKeystrokes = [
		1065 // CTRL + A
	];
	*/
	config.removeFormatAttributes = 'lang,width,height,align,hspace,valign'; // style,class
	config.removeFormatTags = 'b,big,code,del,dfn,em,font,i,ins,kbd,q,pre,samp,small,strike,strong,sub,sup,tt,u,var';
	config.scayt_autoStartup = false;
	config.scayt_contextCommands = 'off';
	config.enterMode = CKEDITOR.ENTER_BR;
	config.shiftEnterMode = CKEDITOR.ENTER_BR;
	config.coreStyles_bold = {element:'span',attributes:{'style':'font-weight: bold;'}};
	config.coreStyles_italic = {element:'span',attributes:{'style':'font-style: italic;'}};
	config.coreStyles_underline = {element:'span',attributes:{'style':'text-decoration: underline;'}};
	//config.contentsCss = ['/css/portal.css'];
	//config.removeDialogTabs = 'flash:advanced;image:Link';
	config.fullPage = false;
	config.toolbar_designer =
	[
		['Source','-','Preview','-'],
		['Cut','Copy','Paste','PasteFromWord'],
		['Undo','Redo','-','SelectAll','RemoveFormat'],
		['Link','Unlink','Anchor','Image','HorizontalRule'],
		['Maximize', 'ShowBlocks'],['Table'],
		'/',
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		[,'printonly','swcomment','swdata'],['fdmadd','swxmlmarkeradd'],['pvmins'],['glmenushow','gladdexpress','gladd'],//,'swmetadata','hiddenuser'
		'/',
		['Styles','Format','Font','FontSize'],
		['TextColor','BGColor']
	];
	config.extraPlugins = 'swtags,swglossary,swfreedocmarkers,swparametervalue,swxmlmarkers';
	config.toolbar = 'user';
	config.toolbar_user =
	[
		['Undo','Redo','-','RemoveFormat'],
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['Font','FontSize','TextColor','BGColor'],
		['Link','Unlink','Anchor'],['glmenushow','gladdexpress','gladd']
		['Maximize','ShowBlocks'],['Table']
	];
	/*
	config.toolbar =
	[
		['Table','HorizontalRule','SpecialChar','-','Cut','Copy','Paste','PasteText','PasteFromWord','RemoveFormat','-','Undo','Redo','-','Find','Replace','-','Maximize','-','About'],
		'/',
		['Bold','Italic','Underline','Strike','-','TextColor','BGColor','-','NumberedList','BulletedList','-','Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		'/',
		['Format','Font','FontSize']
	];
	*/
	
	config.toolbar_minimal =
	[
		['Source'],//,'-','Save','NewPage','Preview','-','Templates'],
		//['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
		//['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		//['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
		//'/',
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		//['BidiLtr', 'BidiRtl' ],
		//['Link','Unlink','Anchor'],
		//['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
		'/',
		['Styles','Format'/*,'Font','FontSize'*/],
		//['TextColor','BGColor'],
		['Maximize', 'ShowBlocks']
	];
	
	config.toolbarStartupExpanded = false;
};
