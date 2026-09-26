import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import save from './save';
import type { ChaptersAttributes } from './types';

registerBlockType< ChaptersAttributes >( metadata.name, {
	...metadata,
	edit: Edit,
	save,
} );
