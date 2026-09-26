import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import save from './save';
import type { FormSubmissionNotificationAttributes } from './types';

registerBlockType< FormSubmissionNotificationAttributes >( metadata.name, {
	...metadata,
	edit: Edit,
	save,
} );
