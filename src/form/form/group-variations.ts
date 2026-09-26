/**
 * core/group variations — a plain wrapper with tagName:"fieldset"/"legend"
 * (a real HTML mechanism `core/group` already has; the Inspector's own
 * "HTML element" picker just doesn't expose these two values from its
 * fixed list — the underlying attribute has no such restriction).
 * Registered here (imported by axell/form's own index.tsx, always loaded
 * in the block editor) rather than needing a new
 * enqueue_block_editor_assets hook — see this plugin's CLAUDE.md for why
 * axell/form-fieldset itself stays a real custom block instead of also
 * becoming a variation (it needs a stateful "Add legend"/"Remove legend"
 * toolbar toggle, which variations can't provide).
 */
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

registerBlockVariation( 'core/group', {
	name: 'form-fieldset-group',
	title: __( 'Fieldset (plain)', 'axellcore-atelierclub' ),
	description: __(
		'A plain grouping wrapper rendered as a real <fieldset> — no legend. For a fieldset with a toggleable legend, use the Form Fieldset block instead.',
		'axellcore-atelierclub'
	),
	icon: 'editor-table',
	attributes: { tagName: 'fieldset' },
	scope: [ 'inserter', 'transform' ],
	isActive: ( blockAttributes ) => blockAttributes.tagName === 'fieldset',
} );

registerBlockVariation( 'core/group', {
	name: 'form-legend-group',
	title: __( 'Legend (plain)', 'axellcore-atelierclub' ),
	description: __(
		'A plain wrapper rendered as a real <legend> — for hand-composing a fieldset from plain groups instead of the Form Fieldset block.',
		'axellcore-atelierclub'
	),
	icon: 'editor-textcolor',
	attributes: { tagName: 'legend' },
	scope: [ 'inserter', 'transform' ],
	isActive: ( blockAttributes ) => blockAttributes.tagName === 'legend',
} );
