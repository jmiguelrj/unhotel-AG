import { Modal, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useFields } from 'jet-form-builder-blocks-to-actions';
import { useSelect } from '@wordpress/data';
import {
	StickyModalActions,
	ModalFooterStyle,
} from 'jet-form-builder-components';
import EditWCDetailModalItem from './EditWCDetailModalItem';

const {
	      Repeater,
	      RepeaterAddNew,
	      RepeaterState,
      } = JetFBComponents;

function EditWCDetailsModal( { setIsShow } ) {
	const [ isLoading, setLoading ] = useState( false );

	const [ details, setDetails ] = useState(
		() => JetAppointmentActionData.details,
	);

	const formFields = useFields( { withInner: false, placeholder: '--' } );
	const formId     = useSelect(
		select => (
			select( 'core/editor' ).getEditedPostAttribute( 'id' )
		),
		[],
	);

	function saveWCDetails() {
		setLoading( true );

		jQuery.ajax( {
			url: window.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'jet_appointments_save_wc_details',
				post_id: formId,
				nonce: JetAppointmentActionData.nonce,
				details,
			},
		} ).done( function ( response ) {
			if ( !response.success ) {
				alert( response.data.message );
			}
			else {
				JetAppointmentActionData.details = details;
				setIsShow( false );
			}

		} ).fail( function ( jqXHR, textStatus, errorThrown ) {
			alert( errorThrown );
		} ).always( () => {
			setLoading( false );
		} );
	}

	const updateItems = items => {
		setDetails( items( details ) );
	};

	return <Modal
		size="large"
		title={ __(
			'Set up WooCommerce order details',
			'jet-booking',
		) }
		onRequestClose={ () => setIsShow( false ) }
		className={ ModalFooterStyle }
	>
		<RepeaterState state={ updateItems }>
			<Repeater items={ details }>
				<EditWCDetailModalItem formFields={ formFields }/>
			</Repeater>
			<RepeaterAddNew>
				{ __( 'Add New Item', 'jet-booking' ) }
			</RepeaterAddNew>
		</RepeaterState>
		<StickyModalActions>
			<Button
				isPrimary
				onClick={ saveWCDetails }
				disabled={ isLoading }
				isBusy={ isLoading }
			>
				{ __( 'Update', 'jet-booking' ) }
			</Button>
			<Button
				isSecondary
				disabled={ isLoading }
				onClick={ () => setIsShow( false ) }
			>
				{ __( 'Cancel', 'jet-booking' ) }
			</Button>
		</StickyModalActions>
	</Modal>;
}

export default EditWCDetailsModal;