/**
 * Atelier Axell Club — shared frontend behavior.
 * Ported 1:1 from the approved atelier-axell-club.html mockup's inline <script>,
 * adapted to work against class/data-attribute hooks instead of hardcoded element IDs
 * so it works regardless of which block instance renders the markup.
 */
( function () {
	'use strict';

	/* ---------------------------------------------------------------------
	 * Nav scroll state — adds .aac-scrolled to .aac-nav past 40px of scroll.
	 * ------------------------------------------------------------------- */
	var nav = document.querySelector( '.aac-nav' );
	if ( nav ) {
		var onScroll = function () {
			if ( window.scrollY > 40 ) {
				nav.classList.add( 'aac-scrolled' );
			} else {
				nav.classList.remove( 'aac-scrolled' );
			}
		};
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}

	/* ---------------------------------------------------------------------
	 * Reveal on scroll — any element already carrying .aac-reveal (set via
	 * the block's "Additional CSS class(es)" field at content-authoring
	 * time) gets .aac-in the first time it enters the viewport.
	 * ------------------------------------------------------------------- */
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'aac-in' );
						io.unobserve( entry.target );
					}
				} );
			},
			{ threshold: 0.1 }
		);
		document.querySelectorAll( '.aac-reveal' ).forEach( function ( el ) {
			io.observe( el );
		} );
	} else {
		document.querySelectorAll( '.aac-reveal' ).forEach( function ( el ) {
			el.classList.add( 'aac-in' );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Input masks — driven by data-aac-mask="cpf-cnpj|cep|phone" on the
	 * <input>. A cpf-cnpj field additionally reads a sibling <select> named
	 * via data-aac-mask-source="<field name>" within the same <form> to
	 * decide which format to apply (mirrors the source's handleDocTypeChange()
	 * cross-field behavior).
	 * ------------------------------------------------------------------- */
	function digitsOnly( v ) {
		return v.replace( /\D/g, '' );
	}

	function maskCpfCnpj( input, docType ) {
		var v = digitsOnly( input.value );
		if ( docType === 'cnpj' ) {
			v = v.slice( 0, 14 );
			v = v
				.replace( /^(\d{2})(\d)/, '$1.$2' )
				.replace( /^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3' )
				.replace( /\.(\d{3})(\d)/, '.$1/$2' )
				.replace( /(\d{4})(\d)/, '$1-$2' );
		} else {
			v = v.slice( 0, 11 );
			v = v
				.replace( /(\d{3})(\d)/, '$1.$2' )
				.replace( /(\d{3})(\d)/, '$1.$2' )
				.replace( /(\d{3})(\d{1,2})$/, '$1-$2' );
		}
		input.value = v;
	}

	function maskCep( input ) {
		var v = digitsOnly( input.value ).slice( 0, 8 );
		if ( v.length > 5 ) {
			v = v.slice( 0, 5 ) + '-' + v.slice( 5 );
		}
		input.value = v;
	}

	function maskPhone( input ) {
		var v = digitsOnly( input.value ).slice( 0, 11 );
		if ( v.length > 10 ) {
			v = v.replace( /^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3' );
		} else if ( v.length > 6 ) {
			v = v.replace( /^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3' );
		} else if ( v.length > 2 ) {
			v = v.replace( /^(\d{2})(\d{0,5}).*/, '($1) $2' );
		} else if ( v.length > 0 ) {
			v = v.replace( /^(\d*)/, '($1' );
		}
		input.value = v;
	}

	function docTypePlaceholder( docType ) {
		if ( docType === 'cpf' ) {
			return { placeholder: '000.000.000-00', maxlength: '14' };
		}
		if ( docType === 'cnpj' ) {
			return { placeholder: '00.000.000/0000-00', maxlength: '18' };
		}
		return { placeholder: '000.000.000-00 / 00.000.000/0000-00', maxlength: null };
	}

	document.querySelectorAll( '[data-aac-mask="cpf-cnpj"]' ).forEach( function ( input ) {
		var form = input.closest( 'form' );
		var sourceName = input.getAttribute( 'data-aac-mask-source' );
		var source = sourceName && form ? form.elements.namedItem( sourceName ) : null;

		var applyDocType = function () {
			var docType = source ? source.value : '';
			var cfg = docTypePlaceholder( docType );
			input.placeholder = cfg.placeholder;
			if ( cfg.maxlength ) {
				input.setAttribute( 'maxlength', cfg.maxlength );
			} else {
				input.removeAttribute( 'maxlength' );
			}
			input.value = '';
		};

		if ( source ) {
			source.addEventListener( 'change', applyDocType );
		}

		input.addEventListener( 'input', function () {
			maskCpfCnpj( input, source ? source.value : '' );
		} );
	} );

	document.querySelectorAll( '[data-aac-mask="cep"]' ).forEach( function ( input ) {
		input.addEventListener( 'input', function () {
			maskCep( input );
		} );
	} );

	document.querySelectorAll( '[data-aac-mask="phone"]' ).forEach( function ( input ) {
		input.addEventListener( 'input', function () {
			maskPhone( input );
		} );
	} );

	/* ---------------------------------------------------------------------
	 * Submit handler — client-side only for this phase (no backend yet).
	 * Any <form data-aac-club-form> gets the exact same UX as the source
	 * mockup: validate, reset, alert. Real submission (WP user creation,
	 * CSV/ERP export, etc.) is a later phase, not wired here.
	 * ------------------------------------------------------------------- */
	document.querySelectorAll( 'form[data-aac-club-form]' ).forEach( function ( form ) {
		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			if ( ! form.checkValidity() ) {
				form.reportValidity();
				return;
			}
			form.reset();
			window.alert(
				'Sua solicitação foi enviada.\n\nA curadoria Axell entrará em contato em breve com o próximo passo.\n\nBem-vindo(a) ao Atelier.'
			);
		} );
	} );
} )();
