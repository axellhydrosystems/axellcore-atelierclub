import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import save from './save';
import icon from './icon';
import type { FormAttributes } from './types';
import './group-variations';

registerBlockType< FormAttributes >( metadata.name, {
	...metadata,
	icon,
	edit: Edit,
	save,
} );
