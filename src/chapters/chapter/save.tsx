import {
	useBlockProps,
	useInnerBlocksProps,
	RichText,
} from '@wordpress/block-editor';
import type { BlockSaveProps } from '@wordpress/blocks';
import type { ChapterAttributes } from './types';

export default function save( {
	attributes,
}: BlockSaveProps< ChapterAttributes > ) {
	const blockProps = useBlockProps.save( { className: 'aac-chapter' } );
	const innerBlocksProps = useInnerBlocksProps.save( {
		className: 'aac-chapter-body',
	} );

	return (
		<div { ...blockProps }>
			<p className="aac-chapter-tag">
				<RichText.Content
					tagName="span"
					className="aac-chapter-label"
					value={ attributes.label }
				/>
			</p>
			<div { ...innerBlocksProps } />
		</div>
	);
}
