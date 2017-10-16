<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

abstract class RP4WP_Constants {

	// Link title
	const LINK_PT = 'rp4wp_link';

	// Linked meta
	const PM_PT_PARENT = 'rp4wp_pt_parent';
	const PM_PARENT = 'rp4wp_parent';
	const PM_CHILD = 'rp4wp_child';
	const PM_MANUAL = 'rp4wp_manual';

	// Post meta
	const PM_POST_AUTO_LINKED = 'rp4wp_auto_linked'; // Posts that have automatically linked posts

	// Options
	const OPTION_DO_INSTALL = 'rp4wp_premium_start_install';
	const OPTION_IS_INSTALLING = 'rp4wp_is_installing';

	// Nag options
	const OPTION_INSTALL_DATE = 'rp4wp_install_date';
	const OPTION_ADMIN_NOTICE_KEY = 'rp4wp_hide_nag';

	// Nonce
	const NONCE_INSTALL = 'rp4wp-install-secret';
	const NONCE_AJAX = 'rp4wp-ajax-nonce-omgrandomword';

	// Installed Post Types
	const OPTION_INSTALLED_PT = 'rp4wp_installed_post_types';

	// Option current version
	const OPTION_CURRENT_VERSION = 'rp4wp_current_version';

	// Option with excluded ID's
	CONST OPTION_EXCLUDED = 'rp4wp_excluded';

	// This.Is.Premium.
	const PREMIUM = true;

	// Component transients
	const TRANSIENT_COMPONENTS = 'rp4wp_components';
	const TRANSIENT_COMPONENT_CSS = 'rp4wp_component_css';

	// Joined Words transient
	const TRANSIENT_JOINED_WORDS = 'rp4wp_joined_words';

	// Extra ignored words
	const TRANSIENT_EXTRA_IGNORED_WORDS = 'rp4wp_extra_ignored_words';

}