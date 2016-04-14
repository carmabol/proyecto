/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


var client = new ZeroClipboard( $("#click-to-copy"), {
              moviePath: "http://davidwalsh.name/demo/ZeroClipboard.swf",
              debug: false
});

client.on( "copy", function(event) {
  var clipboard = event.clipboardData;
  clipboard.setData( "text/plain", "Copy me!" );
  clipboard.setData( "text/html", "<b>Copy me!</b>" );
  clipboard.setData( "application/rtf", "{\\rtf1\\ansi\n{\\b Copy me!}}" );
 /*
// alert( "movie is loaded" );
  //--  $('#flash-loaded').fadeIn();
  client.on( "complete", function(client, args) {
    // `this` is the element that was clicked
    client.setText( "Set text copied." );
     $('#click-to-copy-text').fadeIn();
  } );*/
} );