/**
 * axellcore/form — <form> wrapper block. Static (no PHP render): the
 * submission is client-side only in this phase (assets/js/frontend.js
 * listens for `[data-aac-club-form]`). No build step — plain browser JS
 * against the wp.* globals, same pattern as core's own pre-@wordpress/scripts
 * blocks.
 */
( function ( blocks, element, blockEditor ) {
	var el = element.createElement;
	var useBlockProps = blockEditor.useBlockProps;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;

	var ALLOWED_BLOCKS = [
		'core/heading',
		'core/paragraph',
		'core/group',
		'core/columns',
		'core/column',
		'core/list',
		'core/list-item',
		'core/buttons',
		'core/button',
		'axellcore/form-input',
	];

	var TEMPLATE = [ [ 'axellcore/form-input', {} ] ];

	function formProps( extra ) {
		return Object.assign(
			{
				'data-aac-club-form': '',
				noValidate: true,
			},
			extra || {}
		);
	}

	blocks.registerBlockType( 'axellcore/form', {
		edit: function () {
			var blockProps = useBlockProps( { className: 'aac-apply-form' } );
			var innerBlocksProps = useInnerBlocksProps(
				formProps( blockProps ),
				{
					allowedBlocks: ALLOWED_BLOCKS,
					template: TEMPLATE,
					templateLock: false,
				}
			);
			return el( 'form', innerBlocksProps );
		},
		save: function () {
			var blockProps = useBlockProps.save( { className: 'aac-apply-form' } );
			var innerBlocksProps = useInnerBlocksProps.save( formProps( blockProps ) );
			return el( 'form', innerBlocksProps );
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor );
