/**
 * axellcore/form-input — a single form field. Static (no PHP render), no
 * build step (plain browser JS against wp.* globals). Renders text/email/
 * url/number/tel/textarea/select/checkbox/hidden, matching the markup the
 * plugin's assets/css/sections.css (ported from atelier-axell-club.html)
 * expects: `.aac-field` wrapper (or `.aac-consent` for the consent variant).
 *
 * Source strings are English; translations live in languages/*.po (see
 * bin/release.sh + `wp i18n make-pot`).
 */
( function ( blocks, element, blockEditor, components, richText, i18n ) {
	var el = element.createElement;
	var Fragment = element.Fragment;
	var useBlockProps = blockEditor.useBlockProps;
	var RichText = blockEditor.RichText;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ToggleControl = components.ToggleControl;
	var __ = i18n.__;

	var TYPE_OPTIONS = [
		{ label: __( 'Text', 'axellcore-atelierclub' ), value: 'text' },
		{ label: __( 'Email', 'axellcore-atelierclub' ), value: 'email' },
		{ label: __( 'URL', 'axellcore-atelierclub' ), value: 'url' },
		{ label: __( 'Number', 'axellcore-atelierclub' ), value: 'number' },
		{ label: __( 'Phone', 'axellcore-atelierclub' ), value: 'tel' },
		{ label: __( 'Textarea', 'axellcore-atelierclub' ), value: 'textarea' },
		{ label: __( 'Select (options)', 'axellcore-atelierclub' ), value: 'select' },
		{ label: __( 'Checkbox', 'axellcore-atelierclub' ), value: 'checkbox' },
		{ label: __( 'Hidden', 'axellcore-atelierclub' ), value: 'hidden' },
	];

	var MASK_OPTIONS = [
		{ label: __( '— none —', 'axellcore-atelierclub' ), value: '' },
		{ label: __( 'CPF / CNPJ', 'axellcore-atelierclub' ), value: 'cpf-cnpj' },
		{ label: __( 'CEP (postal code)', 'axellcore-atelierclub' ), value: 'cep' },
		{ label: __( 'Phone', 'axellcore-atelierclub' ), value: 'phone' },
	];

	function optionsToText( options ) {
		return ( options || [] )
			.map( function ( o ) {
				return ( o.label || '' ) + '|' + ( o.value || '' );
			} )
			.join( '\n' );
	}

	function textToOptions( text ) {
		return text
			.split( '\n' )
			.map( function ( line ) {
				return line.trim();
			} )
			.filter( Boolean )
			.map( function ( line ) {
				var parts = line.split( '|' );
				return {
					label: ( parts[ 0 ] || '' ).trim(),
					value: ( parts[ 1 ] !== undefined ? parts[ 1 ] : parts[ 0 ] || '' ).trim(),
				};
			} );
	}

	/**
	 * Build the field's inner control element (input/select/textarea), shared
	 * between edit() (a disabled preview) and save() (the real static markup).
	 */
	function FieldControl( attributes, isSave ) {
		var type = attributes.type;
		var name = attributes.name;
		var required = attributes.required;
		var placeholder = attributes.placeholder;

		var common = {
			name: name,
			required: required || undefined,
			placeholder: placeholder || undefined,
		};

		if ( isSave ) {
			if ( attributes.mask ) {
				common[ 'data-aac-mask' ] = attributes.mask;
			}
			if ( attributes.maskSourceName ) {
				common[ 'data-aac-mask-source' ] = attributes.maskSourceName;
			}
			if ( attributes.citiesSourceName ) {
				common[ 'data-aac-cities-source' ] = attributes.citiesSourceName;
				// Starts empty (populated by frontend.js once the source
				// field has a value) — disabled until then, same as the
				// real page markup this generates.
				common.disabled = true;
			}
		} else {
			common.disabled = true;
		}

		if ( 'textarea' === type ) {
			return el( 'textarea', common );
		}

		if ( 'select' === type ) {
			var options = attributes.options || [];
			return el(
				'select',
				common,
				el( 'option', { value: '' }, placeholder || __( 'Select an option', 'axellcore-atelierclub' ) ),
				options.map( function ( o, i ) {
					return el( 'option', { key: i, value: o.value }, o.label );
				} )
			);
		}

		if ( 'checkbox' === type ) {
			return el(
				'input',
				Object.assign( {}, common, {
					type: 'checkbox',
					checked: isSave ? undefined : !! attributes.checked,
					defaultChecked: isSave ? !! attributes.checked : undefined,
				} )
			);
		}

		if ( 'hidden' === type ) {
			return el( 'input', { type: 'hidden', name: name, value: attributes.value || '' } );
		}

		return el( 'input', Object.assign( {}, common, { type: type } ) );
	}

	/**
	 * Merge a base className into a wrapperProps object (as produced by
	 * useBlockProps()/useBlockProps.save()), so the block's own root element
	 * IS `.aac-field`/`.aac-consent` — no extra wrapping div, which would
	 * otherwise break `.aac-form-row`'s CSS grid (it expects `.aac-field` as
	 * a direct child).
	 */
	function withBase( wrapperProps, base ) {
		var classes = [ base ];
		if ( wrapperProps && wrapperProps.className ) {
			classes.push( wrapperProps.className );
		}
		return Object.assign( {}, wrapperProps, { className: classes.join( ' ' ) } );
	}

	function FieldWrapper( attributes, labelElement, fieldElement, wrapperProps ) {
		if ( 'hidden' === attributes.type ) {
			return fieldElement;
		}

		if ( 'consent' === attributes.variant ) {
			return el(
				'label',
				withBase( wrapperProps, 'aac-consent' ),
				fieldElement,
				labelElement
			);
		}

		return el(
			'div',
			withBase( wrapperProps, 'aac-field' ),
			el(
				'label',
				null,
				labelElement,
				attributes.required ? el( 'span', { className: 'aac-req' }, ' *' ) : null
			),
			fieldElement,
			attributes.hint ? el( 'div', { className: 'aac-hint' }, attributes.hint ) : null
		);
	}

	blocks.registerBlockType( 'axellcore/form-input', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

			var labelEl = el( RichText, {
				tagName: 'span',
				className: 'aac-field-label-text',
				value: attributes.label,
				onChange: function ( v ) {
					setAttributes( { label: v } );
				},
				placeholder: __( 'Field label…', 'axellcore-atelierclub' ),
				allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ],
			} );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Field', 'axellcore-atelierclub' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Type', 'axellcore-atelierclub' ),
							value: attributes.type,
							options: TYPE_OPTIONS,
							onChange: function ( v ) {
								setAttributes( { type: v } );
							},
						} ),
						el( TextControl, {
							label: __( 'Field name (name attribute)', 'axellcore-atelierclub' ),
							value: attributes.name,
							onChange: function ( v ) {
								setAttributes( { name: v } );
							},
						} ),
						'checkbox' === attributes.type &&
							el( SelectControl, {
								label: __( 'Variant', 'axellcore-atelierclub' ),
								value: attributes.variant,
								options: [
									{ label: __( 'Default', 'axellcore-atelierclub' ), value: 'field' },
									{ label: __( 'Consent (LGPD)', 'axellcore-atelierclub' ), value: 'consent' },
								],
								onChange: function ( v ) {
									setAttributes( { variant: v } );
								},
							} ),
						'select' !== attributes.type &&
							'checkbox' !== attributes.type &&
							'hidden' !== attributes.type &&
							el( TextControl, {
								label: __( 'Placeholder', 'axellcore-atelierclub' ),
								value: attributes.placeholder,
								onChange: function ( v ) {
									setAttributes( { placeholder: v } );
								},
							} ),
						'hidden' === attributes.type &&
							el( TextControl, {
								label: __( 'Value', 'axellcore-atelierclub' ),
								value: attributes.value,
								onChange: function ( v ) {
									setAttributes( { value: v } );
								},
							} ),
						'checkbox' === attributes.type &&
							el( ToggleControl, {
								label: __( 'Checked by default', 'axellcore-atelierclub' ),
								checked: !! attributes.checked,
								onChange: function ( v ) {
									setAttributes( { checked: v } );
								},
							} ),
						'hidden' !== attributes.type &&
							'checkbox' !== attributes.type &&
							el( ToggleControl, {
								label: __( 'Required', 'axellcore-atelierclub' ),
								checked: !! attributes.required,
								onChange: function ( v ) {
									setAttributes( { required: v } );
								},
							} ),
						'field' === attributes.variant &&
							'hidden' !== attributes.type &&
							el( TextControl, {
								label: __( 'Hint text (optional)', 'axellcore-atelierclub' ),
								value: attributes.hint,
								onChange: function ( v ) {
									setAttributes( { hint: v } );
								},
							} ),
						'select' === attributes.type &&
							el( TextareaControl, {
								label: __( 'Options (one per line: Label|value)', 'axellcore-atelierclub' ),
								help: __( 'E.g.: Individual · CPF|cpf', 'axellcore-atelierclub' ),
								value: optionsToText( attributes.options ),
								onChange: function ( v ) {
									setAttributes( { options: textToOptions( v ) } );
								},
							} ),
						'select' === attributes.type &&
							el( TextControl, {
								label: __( 'Field (name) that populates these options', 'axellcore-atelierclub' ),
								help: __( 'Leave empty for a static list. If set, this select starts empty/disabled and assets/js/frontend.js fetches its options from the REST cities endpoint whenever that field changes.', 'axellcore-atelierclub' ),
								value: attributes.citiesSourceName,
								onChange: function ( v ) {
									setAttributes( { citiesSourceName: v } );
								},
							} )
					),
					el(
						PanelBody,
						{ title: __( 'Input mask (advanced)', 'axellcore-atelierclub' ), initialOpen: false },
						el( SelectControl, {
							label: __( 'Mask', 'axellcore-atelierclub' ),
							value: attributes.mask,
							options: MASK_OPTIONS,
							onChange: function ( v ) {
								setAttributes( { mask: v } );
							},
						} ),
						'cpf-cnpj' === attributes.mask &&
							el( TextControl, {
								label: __( 'Field (name) that decides CPF vs. CNPJ', 'axellcore-atelierclub' ),
								help: __( 'Name of the select field whose value ("cpf"/"cnpj") drives this mask.', 'axellcore-atelierclub' ),
								value: attributes.maskSourceName,
								onChange: function ( v ) {
									setAttributes( { maskSourceName: v } );
								},
							} )
					)
				),
				FieldWrapper( attributes, labelEl, FieldControl( attributes, false ), blockProps )
			);
		},
		save: function ( props ) {
			var attributes = props.attributes;
			var blockProps = useBlockProps.save();
			var labelEl = el( RichText.Content, {
				tagName: 'span',
				className: 'aac-field-label-text',
				value: attributes.label,
			} );
			return FieldWrapper( attributes, labelEl, FieldControl( attributes, true ), blockProps );
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.richText, window.wp.i18n );
