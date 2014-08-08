YUI.add('moodle-atto_structure-button', function (Y, NAME) {

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @package    atto_structure
 * @copyright  COPYRIGHTINFO
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_structure-button
 */

/**
 * Atto text editor structure plugin.
 *
 * @namespace M.atto_structure
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_structure';
var WIDTHCONTROL = 'structure_width';
var HEIGHTCONTROL = 'structure_height';
var LOGNAME = 'atto_structure';
var CSS = {
        INPUTSUBMIT: 'atto_media_urlentrysubmit',
        INPUTCANCEL: 'atto_media_urlentrycancel',
        WIDTHCONTROL: 'widthcontrol',
        HEIGHTCONTROL: 'heightcontrol'
    },
    SELECTORS = {
        WIDTHCONTROL: '.widthcontrol',
        HEIGHTCONTROL: '.heightcontrol'
    };

var TEMPLATE = '' +
//    '<link type="text/css" rel="stylesheet" href="http://localhost/marvinjs-14.7.7/css/doc.css" />' +
//    '<link type="text/css" rel="stylesheet" href="http://localhost/marvinjs-14.7.7/js/lib/rainbow/github.css" />' +
//    '<script type="text/javascript" src="http://localhost/marvinjs-14.7.7/js/lib/rainbow/rainbow-custom.min.js"></script>' +
//    '<script type="text/javascript" src="http://localhost/marvinjs-14.7.7/gui/gui.nocache.js"></script>' +
//    '<script type="text/javascript" src="http://localhost/marvinjs-14.7.7/js/promise-0.1.1.min.js"></script>' +
//    '<script type="text/javascript" src="http://localhost/marvinjs-14.7.7/js/marvinjslauncher.js"></script>' +
    '<form class="atto_form">' +
        '<div id="{{elementid}}_{{innerform}}" class="mdl-align">' +
            '<strong>{{get_string "instructions" component}}</strong>' +
            '<table><tr><td><label for="{{elementid}}_{{WIDTHCONTROL}}">{{get_string "width" component}}</label></td>' +
            '<td><input class="{{CSS.WIDTHCONTROL}}" size="6" id="{{elementid}}_{{WIDTHCONTROL}}" name="{{elementid}}_{{WIDTHCONTROL}}" value="{{defaultwidth}}" /></td><td></td></tr>' +
            '<tr><td><label for="{{elementid}}_{{HEIGHTCONTROL}}">{{get_string "height" component}}</label></td>' +
            '<td><input class="{{CSS.HEIGHTCONTROL}}" size="6" id="{{elementid}}_{{HEIGHTCONTROL}}" name="{{elementid}}_{{HEIGHTCONTROL}}" value="{{defaultheight}}" /><td>' +
            '<button class="{{CSS.INPUTSUBMIT}}">{{get_string "insert" component}}</button></td></tr></table>' +
        '</div>' +
        'icon: {{clickedicon}}'  +
//        '<iframe src="http://localhost/marvinjs-14.7.7/editor.html" id="MSketch" class="sketcher-frame" height="510px"></iframe>' +
    '</form>';

Y.namespace('M.atto_structure').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

  
    /**
     * Initialize the button
     *
     * @method Initializer
     */
    initializer: function() {
        // If we don't have the capability to view then give up.
        if (this.get('disabled')){
            return;
        }

        //var twoicons = ['icon'];

        //Y.Array.each(twoicons, function(theicon) {
            // Add the structure icon/buttons

            this.addButton({
                icon: 'icon',
                iconComponent: 'atto_structure',
                buttonName: 'icon',
                callback: this._displayDialogue,
                callbackArgs: 'icon'
            });
        //}, this);

    },

    /**
     * Get the id of the flavor control where we store the ice cream flavor
     *
     * @method _getFlavorControlName
     * @return {String} the name/id of the flavor form field
     * @private
     */
/*
    _getFlavorControlName: function(){
        return(this.get('host').get('elementid') + '_' + WIDTHCONTROL);
    }, */

     /**
     * Display the structure Dialogue
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function(e, clickedicon) {
        e.preventDefault();
        //var width='auto';


        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
            width: '768px',
            focusAfterHide: clickedicon
        });
        //dialog doesn't detect changes in width without this
        //if you reuse the dialog, this seems necessary
      /*  if(dialogue.width !== width + 'px'){
            dialogue.set('width',width+'px');
        }  */

        var iframe = Y.Node.create('<iframe></iframe>');
        iframe.setStyles({
            height: '510px',
            border: 'none',
            width: '100%'
        });
        iframe.setAttribute('src', this._getIframeURL());
        iframe.setAttribute('id', 'sketch');
        iframe.setAttribute('data-toolbars', 'education');


        //iframe.setAttribute('src', this._getIframeURL());

        //append buttons to iframe
        var buttonform = this._getFormContent(clickedicon);

        var bodycontent =  Y.Node.create('<div></div>');
        bodycontent.append(buttonform).append(iframe);
        //bodycontent.append(buttonform);

        //set to bodycontent
        dialogue.set('bodyContent', bodycontent);
        dialogue.show();
        this.markUpdated();
    },


     /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getDialogueContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getFormContent: function(clickedicon) {
        var template = Y.Handlebars.compile(TEMPLATE),
            content = Y.Node.create(template({
                elementid: this.get('host').get('elementid'),
                CSS: CSS,
                WIDTHCONTROL: WIDTHCONTROL,
                HEIGHTCONTROL: HEIGHTCONTROL,
                component: COMPONENTNAME,
                defaultwidth: this.get('defaultwidth'),
                defaultheight: this.get('defaultheight'),
                clickedicon: clickedicon
            }));
        //console.log('getFormContent');
        //console.log(this);
        this._form = content;
        this._form.one('.' + CSS.INPUTSUBMIT).on('click', this._getImgURL, this);
        //this._form.one('.' + CSS.INPUTSUBMIT).on('click', this._getImage, this);
        return content;
    },



    _getIframeURL: function() {
        //return M.cfg.wwwroot + '/lib/editor/atto/plugins/structure/dialog/marvinjs.php';



        return this.get('path')+'/editor.html';

/* + 
          'itemid='+ this._itemid + '&recorder=' + therecorder + '&usewhiteboard=' + this._usewhiteboard  + 
          '&updatecontrol=' + this._getFilenameControlName();  */
    },



  _getImgURL: function(e) {

        e.preventDefault();
    var dialog = this.getDialogue({
            focusAfterHide: null
        }).hide();  


                            var widthcontrol = this._form.one(SELECTORS.WIDTHCONTROL);
			    var newwidth = '';
                            // If no file is there to insert, don't do it.
                            if (!widthcontrol.get('value')) {
                                newwidth = this.get('defaultwidth');
                            } else {
                                newwidth = widthcontrol.get('value');
                            } 



                            var heightcontrol = this._form.one(SELECTORS.HEIGHTCONTROL);
			    var newheight = '';
                            // If no file is there to insert, don't do it.
                            if (!heightcontrol.get('value')) {
                                newheight = this.get('defaultheight');
                            } else {
                                newheight = heightcontrol.get('value');
                            } 






//console.log(this);

       // console.log("path="+this.get('path'));


var referringpage = this;
Y.Get.js([this.get('path') + '/gui/gui.nocache.js', this.get('path') + '/js/marvinjslauncher.js', 
this.get('path') + '/js/promise-0.1.1.min.js'], function (err) {
    if (err) {
        Y.log('Error loading JS: ' + err[0].error, 'error');
        return;
    }
    Y.log('file.js loaded successfully!');


               //console.log(referringpage);

		//marvin.onReady(function() {
                //
		//console.log("marvin is ready");
		//});
                                            

           var marvinController;
            MarvinJSUtil.getEditor("#sketch").then(
                function(sketcherInstance) {
                    marvinController = new MarvinControllerClass(
                        sketcherInstance);
            
                    exportPromise = marvinController.sketcherInstance.exportStructure("mrv", null);

                        exportPromise.then(function(source) {


				var imgsettings = {
				 'carbonLabelVisible' : false,
				 'chiralFlagVisible' : true,
				 'valenceErrorVisible' : true,
				 'lonePairsVisible' : true,
				 'implicitHydrogen' : "TERMINAL_AND_HETERO",
				// 'displayMode' : "WIREFRAME",
				 'width' : newwidth,
				 'height' : newheight
				 }


                           // console.log(SELECTORS.WIDTHCONTROL);
                           // console.log('newwidth=' + referringpage._form.one(SELECTORS.WIDTHCONTROL).get('value'));

                            //convert to image
		            imgURL = marvin.ImageExporter.mrvToDataUrl(source, "image/png", imgsettings);

		            divContent ="<div class=\"marvinjs-image\"><img name=\"pict\" src=\"" + imgURL + "\" alt=\"MarvinJS PNG\"/></div>";

                            

			referringpage.editor.focus
		        referringpage.get('host').insertContentAtFocusPoint(divContent);
		        //this.get('host').insertContentAtFocusPoint(divContent);
		        referringpage.markUpdated();


                    });
                   //console.log("here2");
                   //console.log(imgURL._detail);
                   //return imgURL2;
                });
            var MarvinControllerClass = (function() {
                function MarvinControllerClass(
                    sketcherInstance
                ) {
                    this.sketcherInstance =
                        sketcherInstance;
                    this.init();
                }
                MarvinControllerClass.prototype.init =
                    function init() {
                        this.sketcherInstance.setDisplaySettings({
                            "cpkColoring": true,
                            "lonePairsVisible": true,
                            "toolbars": "education"
                        });
                    };
                return MarvinControllerClass;
            }());

//console.log("here3 = "+imgURL);
//return imgURL;
});

//return imgURL;


}


}, { ATTRS: {
        disabled: {
            value: false
        },

        usercontextid: {
            value: null
        },

        defaultwidth: {
            value: '600'
        },
        defaultheight: {
            value: '100'
        },
        path: {
            value: ''
        }
    }
});


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
