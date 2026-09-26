import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import type { BlockSaveProps } from '@wordpress/blocks';
import type { FormSubmissionNotificationAttributes } from './types';

function classesFor(
	attributes: FormSubmissionNotificationAttributes
): string {
	return [ 'aac-notice', `aac-notice-${ attributes.type }` ].join( ' ' );
}

export default function save( {
	attributes,
}: BlockSaveProps< FormSubmissionNotificationAttributes > ) {
	const blockProps = useBlockProps.save( {
		className: classesFor( attributes ),
	} );
	const innerBlocksProps = useInnerBlocksProps.save( {
		...blockProps,
		'data-aac-notice-type': attributes.type,
	} );
	return <div { ...innerBlocksProps } />;
}
