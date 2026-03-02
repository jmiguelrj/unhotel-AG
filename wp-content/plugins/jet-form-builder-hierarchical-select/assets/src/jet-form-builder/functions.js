function getCallbackForAttr( source, objectKey, ifEmptyReturn = '' ) {
	let propertyObject = false;

	if ( objectKey in source ) {
		propertyObject = source[ objectKey ];
	}

	if ( ! propertyObject ) {
		return () => ifEmptyReturn;
	}

	return attr => {
		if ( attr ) {
			return ( propertyObject[ attr ] ? propertyObject[ attr ] : ifEmptyReturn );
		} else {
			return propertyObject;
		}
	};
}

function getFieldSource( fieldId ) {
	const source = window.JetFormBuilderFields[ fieldId ] || {};

	const props = [
		[ '__labels', 'label' ],
		[ '__help', 'help' ],
	];

	props.forEach( ( [ propName, callbackName, ifEmpty = '' ] ) => {
		source[ callbackName ] = getCallbackForAttr( source, propName, ifEmpty );
	} )

	return source;
}

function getBlockSource( block ) {
	const [ nameSpace, blockName ] = block.name.split( '/' );

	return getFieldSource( blockName );
}


export default getBlockSource;