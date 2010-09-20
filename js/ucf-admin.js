(function($){
	$.extend( {
		ucf_admin : function() {
			console.log( 'admin!' );
			
			function test() {
				console.log( 'test!' );
			}
			
			return this;
		}
	} );
	$( function () {
		console.info( 'ucf-admin.js is loaded.' );
		
		var ucf_admin = $.ucf_admin();
		ucf_admin.test();
	} );
})(jQuery)