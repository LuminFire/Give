<?php
/**
 * New Donation Email
 *
 * This class handles all email notification settings.
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_New_Donation_Email' ) ) :

	/**
	 * Give_New_Donation_Email
	 *
	 * @abstract
	 * @since       2.0
	 */
	class Give_New_Donation_Email extends Give_Email_Notification {
		/* @var Give_Payment $payment */
		public $payment;

		/**
		 * Create a class instance.
		 *
		 * @access  public
		 * @since   2.0
		 */
		public function init() {
			$this->id          = 'new-donation';
			$this->label       = __( 'New Donation', 'give' );
			$this->description = __( 'Donation Notification will be sent to recipient(s) when new donation received except offline donation.', 'give' );

			$this->has_recipient_field = true;
			$this->notification_status = 'enabled';
			$this->form_metabox_setting = true;

			// Initialize empty payment.
			$this->payment = new Give_Payment( 0 );

			$this->load();

			add_action( "give_{$this->id}_email_notification", array( $this, 'setup_email_notification' ) );
		}


		/**
		 * Get email subject.
		 *
		 * @since 2.0
		 * @access public
		 * @return string
		 */
		public function get_email_subject() {
			$subject = wp_strip_all_tags( give_get_option( "{$this->id}_email_subject", $this->get_default_email_subject() ) );

			/**
			 * Filters the donation notification subject.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$subject = apply_filters( 'give_admin_donation_notification_subject', $subject, $this->payment );

			/**
			 * Filters the donation notification subject.
			 *
			 * @since 2.0
			 */
			$subject = apply_filters( "give_{$this->id}_get_email_subject", $subject, $this );

			return $subject;
		}


		/**
		 * Get email attachment.
		 *
		 * @since 2.0
		 * @access public
		 * @return string
		 */
		public function get_email_message() {
			$message = give_get_option( "{$this->id}_email_message", $this->get_default_email_message() );

			/**
			 * Filter the email message
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$message = apply_filters( 'give_donation_notification', $message, $this->payment->ID, $this->payment->payment_meta );

			/**
			 * Filter the email message
			 *
			 * @since 2.0
			 */
			$message = apply_filters( "give_{$this->id}_get_default_email_message", $message, $this );

			return $message;
		}


		/**
		 * Get email attachment.
		 *
		 * @since 2.0
		 * @access public
		 * @return array
		 */
		public function get_email_attachments() {
			/**
			 * Filters the donation notification email attachments.
			 * By default, there is no attachment but plugins can hook in to provide one more multiple.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$attachments = apply_filters( 'give_admin_donation_notification_attachments', array(), $this->payment->ID, $this->payment->payment_meta );

			/**
			 * Filters the donation notification email attachments.
			 * By default, there is no attachment but plugins can hook in to provide one more multiple.
			 *
			 * @since 2.0
			 */
			$attachments = apply_filters( "give_{$this->id}_get_email_attachments", $attachments, $this );

			return $attachments;
		}

		/**
		 * Get default email subject.
		 *
		 * @since  2.0
		 * @access public
		 * @return string
		 */
		public function get_default_email_subject() {
			/**
			 * Filter the defaul email subject.
			 *
			 * @since 2.0
			 */
			return apply_filters( "give_{$this->id}_get_default_email_subject", esc_attr__( 'New Donation - #{payment_id}', 'give' ), $this );
		}


		/**
		 * Get default email message.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @return string
		 */
		public function get_default_email_message() {
			/**
			 * Filter the new donation email message
			 *
			 * @since 2.0
			 *
			 * @param string $message
			 */
			return apply_filters( "give_{$this->id}_get_default_email_message", give_get_default_donation_notification_email(), $this );
		}


		/**
		 * Set email data
		 *
		 * @since 2.0
		 */
		public function setup_email_data() {
			/**
			 * Filters the from name.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$from_name = apply_filters( 'give_donation_from_name', Give()->emails->get_from_name(), $this->payment->ID, $this->payment->payment_meta );

			/**
			 * Filters the from email.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$from_email = apply_filters( 'give_donation_from_address', Give()->emails->get_from_address(), $this->payment->ID, $this->payment->payment_meta );

			Give()->emails->__set( 'from_name', $from_name );
			Give()->emails->__set( 'from_email', $from_email );
			Give()->emails->__set( 'heading', esc_html__( 'New Donation!', 'give' ) );
			/**
			 * Filters the donation notification email headers.
			 *
			 * @since 1.0
			 */
			$headers = apply_filters( 'give_admin_donation_notification_headers', Give()->emails->get_headers(), $this->payment->ID, $this->payment->payment_meta );

			Give()->emails->__set( 'headers', $headers );
		}

		/**
		 * Setup email notification.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $payment_id
		 */
		public function setup_email_notification( $payment_id ) {
			$this->payment = new Give_Payment( $payment_id );

			// Set email data.
			$this->setup_email_data();

			// Send email.
			$this->send_email_notification( array( 'payment_id' => $payment_id ) );
		}
	}

endif; // End class_exists check

return Give_New_Donation_Email::get_instance();