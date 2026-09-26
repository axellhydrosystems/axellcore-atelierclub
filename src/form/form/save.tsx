import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import type { BlockSaveProps } from '@wordpress/blocks';
import type { FormAttributes } from './types';

function formProps(
	attributes: FormAttributes,
	extra: Record< string, unknown >
) {
	return {
		...( attributes.submitsToRest
			? { 'data-aac-club-form': '', noValidate: true }
			: {} ),
		...extra,
	};
}

export default function save( {
	attributes,
}: BlockSaveProps< FormAttributes > ) {
	const blockProps = useBlockProps.save();
	const innerBlocksProps = useInnerBlocksProps.save(
		formProps( attributes, blockProps )
	);
	return <form { ...innerBlocksProps } />;
}
