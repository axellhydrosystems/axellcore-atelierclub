import { useBlockProps } from '@wordpress/block-editor';
import type { BlockSaveProps } from '@wordpress/blocks';
import type { FormControlAttributes } from './types';
import ControlElement from './control-element';

export default function save( {
	attributes,
}: BlockSaveProps< FormControlAttributes > ) {
	const blockProps = useBlockProps.save();
	return ControlElement( attributes, true, () => undefined, blockProps );
}
