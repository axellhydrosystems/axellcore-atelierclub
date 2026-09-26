import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import save from './save';
import type { FormLabelAttributes } from './types';
import './style.scss';

registerBlockType< FormLabelAttributes >( metadata.name, {
	...metadata,
	edit: Edit,
	save,
} );
