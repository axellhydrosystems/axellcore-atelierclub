import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import save from './save';
import type { ChapterAttributes } from './types';

registerBlockType< ChapterAttributes >( metadata.name, {
	...metadata,
	edit: Edit,
	save,
} );
