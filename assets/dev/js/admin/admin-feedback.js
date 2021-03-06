/* global jQuery, QazanaAdminFeedbackArgs */
( function( $ ) {
	'use strict';

	var QazanaAdminDialogApp = {

		cacheElements: function() {
			this.cache = {
				$deactivateLink: $( '#the-list' ).find( '[data-slug="qazana"] span.deactivate a' ),
				$dialogHeader: $( '#qazana-deactivate-feedback-dialog-header' ),
				$dialogForm: $( '#qazana-deactivate-feedback-dialog-form' ),
			};
		},

		bindEvents: function() {
			var self = this;

			self.cache.$deactivateLink.on( 'click', function( event ) {
				event.preventDefault();

				self.getModal().show();
			} );
		},

		deactivate: function() {
			location.href = this.cache.$deactivateLink.attr( 'href' );
		},

		initModal: function() {
			var self = this,
				modal;

			self.getModal = function() {
				if ( ! modal ) {
					modal = qazanaAdmin.getDialogsManager().createWidget( 'lightbox', {
						id: 'qazana-deactivate-feedback-modal',
						headerMessage: self.cache.$dialogHeader,
						message: self.cache.$dialogForm,
						hide: {
							onButtonClick: false,
						},
						position: {
							my: 'center',
							at: 'center',
						},
						onReady: function() {
							DialogsManager.getWidgetType( 'lightbox' ).prototype.onReady.apply( this, arguments );

							this.addButton( {
								name: 'submit',
								text: QazanaAdminFeedbackArgs.i18n.submit_n_deactivate,
								callback: self.sendFeedback.bind( self ),
							} );

							if ( ! QazanaAdminFeedbackArgs.is_tracker_opted_in ) {
								this.addButton( {
									name: 'skip',
									text: QazanaAdminFeedbackArgs.i18n.skip_n_deactivate,
									callback: function() {
										self.deactivate();
									},
								} );
							}
						},

						onShow: function() {
							var $dialogModal = $( '#qazana-deactivate-feedback-modal' ),
								radioSelector = '.qazana-deactivate-feedback-dialog-input';

							$dialogModal.find( radioSelector ).on( 'change', function() {
								$dialogModal.attr( 'data-feedback-selected', $( this ).val() );
							} );

							$dialogModal.find( radioSelector + ':checked' ).trigger( 'change' );
						},
					} );
				}

				return modal;
			};
		},

		sendFeedback: function() {
			var self = this,
				formData = self.cache.$dialogForm.serialize();

			self.getModal().getElements( 'submit' ).text( '' ).addClass( 'qazana-loading' );

			$.post( ajaxurl, formData, this.deactivate.bind( this ) );
		},

		init: function() {
			this.initModal();
			this.cacheElements();
			this.bindEvents();
		},
	};

	$( function() {
		QazanaAdminDialogApp.init();
	} );
}( jQuery ) );
