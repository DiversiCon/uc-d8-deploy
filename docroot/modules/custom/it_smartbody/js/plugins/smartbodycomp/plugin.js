/**
 * Smartbody component plugin.
 *
 * I'd like to use a unique custom HTML element for the placeholder instead
 * of using the <hr /> tag.  The problem with that approach is forcing CKE
 * to treat that element as a block element, preventing it from being
 * inserted inside another element and preventing it from being wrapped
 * in p tags.
 *
 * To get this plugin to use a custom HTML element (i.e. <it-smartbody />),
 * simply replace every occurrence below of "hr" to "it-smartbody".
 *
 * Seems like CKEDITOR.dtd may be important:
 *       CKEDITOR.dtd.customtag?
 *       CKEDITOR.dtd.$block.customtag?
 *       CKEDITOR.dtd.body.customtag?
 *
 */

( function() {
  var pluginName = 'smartbodycomp';
  var smartBodyCmd = {
    canUndo: false, // The undo snapshot will be handled by 'insertElement'.
    exec: function( editor ) {
      var placeholder = editor.document.createElement( 'hr' );
      editor.insertElement( placeholder );
    },
    allowedContent: 'hr',
    requiredContent: 'hr'
  };

  // Register a plugin.
  CKEDITOR.plugins.add( pluginName, {
    lang: 'en',
    icons: 'smartbodycomp',
    hidpi: true,
    onLoad: function() {
      CKEDITOR.addCss(
        'hr {' +
          'display: block;' +
          'color: #999999;' +
          'font-size: 80%;' +
          'width: 90%;' +
          'margin: 10px 2%;' +
          'padding: 4px 20px;' +
          'height: 16px;' +
          'border-style: solid;' +
          'border-color: #8c8b8b;' +
          'border-width: 1px 0 0 0;' +
          'border-radius: 8px;' +
          'background: linear-gradient(#ddd, #f9f9f9, #ccc);' +

        '}' +
        'hr:after {' +
          'content: "Smartbody component";' +
          'margin: 2px;' +
        '}'
      );
    },
    init: function( editor ) {
      if ( editor.blockless )
        return;

      editor.addCommand( pluginName, smartBodyCmd );
      editor.ui.addButton && editor.ui.addButton( 'smartbodycomp', {
        label: editor.lang.smartbodycomp.toolbar,
        command: pluginName,
        toolbar: 'insert,40'
      } );
    }
  } );
} )();
