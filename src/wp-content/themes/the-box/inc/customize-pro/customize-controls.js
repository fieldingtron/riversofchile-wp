( function( api ) {

	// Extends our custom "thebox-pro" section.
	api.sectionConstructor['thebox-pro'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );
