import { registerBlockType } from '@wordpress/blocks';
import type { BlockConfiguration } from '@wordpress/blocks';
import metadataJson from './block.json';
import Edit from './edit';
import save from './save';
import icon from './icon';
import type { FormInputAttributes } from './types';
// Not imported here: block.json's editorScript array lists variations.js as
// its own independent entry (see form-control/index.tsx's same note).
import './style.scss';

// block.json's supports.spacing.margin is a fixed-side array
// (["top","bottom"]), which TS widens to string[] from plain JSON —
// stricter than @wordpress/blocks's CSSDirection[] type expects; cast here
// rather than changing the (frozen, legacy) metadata itself.
const metadata =
	metadataJson as unknown as BlockConfiguration< FormInputAttributes >;

registerBlockType< FormInputAttributes >( metadata.name as string, {
	...metadata,
	icon,
	edit: Edit,
	save,
} );
