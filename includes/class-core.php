<?php
/**
 * UM Navigation Menu Core.
 *
 * @since   1.0.0
 * @package UM_Navigation_Menu
 */

/**
 * UM Navigation Menu Core.
 *
 * @since 1.0.0
 */
class UMNM_Core {
	/**
	 * Parent plugin class.
	 *
	 * @since 1.0.0
	 *
	 * @var   UM_Navigation_Menu
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 *
	 * @param  UM_Navigation_Menu $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  1.0.0
	 */
	public function hooks() {
		add_action( 'admin_bar_menu', array( $this, 'add_custom_um_links_to_bar' ), 999 );
	}

	public function add_custom_um_links_to_bar( WP_Admin_Bar $wp_admin_bar ) {
		if ( ! function_exists( 'is_admin_bar_showing' ) ) {
			return;
		}
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		if ( method_exists( $wp_admin_bar, 'get_node' ) ) {
			if ( $wp_admin_bar->get_node( 'user-actions' ) ) {
				$parent = 'user-actions';
			} else {
				return;
			}
		} elseif ( get_option( 'show_avatars' ) ) {
			$parent = 'my-account-with-avatar';
		} else {
			$parent = 'my-account';
		}

		$parent = 'my-account-ultimate-member';

		if ( function_exists( 'UM' ) ) {

			if ( ! UM()->options()->get( 'profile_menu' ) ){
				return;
			}

			// get active tabs
			$tabs = UM()->profile()->tabs_active();

			$tabs = apply_filters( 'um_user_profile_tabs', $tabs );

			UM()->user()->tabs = $tabs;

			// need enough tabs to continue
			if ( count( $tabs ) <= 1 ) {
				return;
			}

			$active_tab = UM()->profile()->active_tab();

			if ( ! isset( $tabs[ $active_tab ] ) ) {
				$active_tab = 'main';
				UM()->profile()->active_tab = $active_tab;
				UM()->profile()->active_subnav = null;
			}

			// Move default tab priority
			$default_tab = UM()->options()->get( 'profile_menu_default_tab' );
			$dtab = ( isset( $tabs[ $default_tab ] ) ) ? $tabs[ $default_tab ] : 'main';
			if ( isset( $tabs[ $default_tab] ) ) {
				unset( $tabs[ $default_tab ] );
				$dtabs[ $default_tab ] = $dtab;
				$tabs = $dtabs + $tabs;
			}
		} else  {
			// get active tabs
			$tabs = $ultimatemember->profile->tabs_active();

			$tabs = apply_filters( 'um_user_profile_tabs', $tabs );

			$ultimatemember->user->tabs = $tabs;

			// need enough tabs to continue
			if ( count( $tabs ) <= 1 ) {
				return;
			}

			$active_tab = $ultimatemember->profile->active_tab();

			if ( ! isset( $tabs[ $active_tab ] ) ) {
				$active_tab = 'main';
				$ultimatemember->profile->active_tab = $active_tab;
				$ultimatemember->profile->active_subnav = null;
			}

			// Move default tab priority
			$default_tab = um_get_option( 'profile_menu_default_tab' );
			$dtab = ( isset( $tabs[ $default_tab ] ) )? $tabs[ $default_tab ] : 'main';
			if ( isset( $tabs[ $default_tab ] ) ) {
				unset( $tabs[ $default_tab ] );
				$dtabs[ $default_tab ] = $dtab;
				$tabs = $dtabs + $tabs;
			}
		}

		$wp_admin_bar->add_menu( array(
			'parent' => 'my-account',
			'id'     => $parent,
			'title'  => __( 'My Profile', 'um-navigation-menu' ),
			'group'  => true,
			'meta'   => array(
				'class' => 'ab-sub-secondary'
			)
		) );


		foreach ( $tabs as $id => $tab ) {

			if ( isset( $tab['hidden'] ) ) {
					continue;
			}

			if ( function_exists( 'UM' ) ) {

				$nav_link = um_user_profile_url( um_user( 'ID' ) );
				$nav_link = remove_query_arg( 'um_action', $nav_link );
				$nav_link = remove_query_arg( 'subnav', $nav_link );
				$nav_link = add_query_arg( 'profiletab', $id, $nav_link );
				$nav_link = apply_filters( "um_profile_menu_link_{$id}", $nav_link );
			} else {
				$nav_link = $ultimatemember->permalinks->get_current_url( get_option( 'permalink_structure' ) );
				$nav_link = um_user_profile_url();
				$nav_link = remove_query_arg( 'um_action', $nav_link );
				$nav_link = remove_query_arg( 'subnav', $nav_link );
				$nav_link = add_query_arg( 'profiletab', $id, $nav_link );
				$nav_link = apply_filters( "um_profile_menu_link_{$id}" , $nav_link );
			}

			$wp_admin_bar->add_menu( array(
				'parent' => $parent,
				'id'     => 'um_profile_menu_link_' . esc_attr( $id ),
				/* Translators: "switch off" means to temporarily log out */
				'title'  => esc_html( $tab['name'] ),
				'href'   => $nav_link,
			) );

			if ( ! empty( $tab['subnav'] ) ) {
				foreach ( $tab['subnav'] as $sub_nav_id => $sub_nav_tab ) {
					$sub_nav_link = add_query_arg( 'subnav', $sub_nav_id, $nav_link );
					$wp_admin_bar->add_menu( array(
						'parent' => 'um_profile_menu_link_' . esc_attr( $id ),
						'id'     => 'um_profile_menu_link_' . esc_attr( $sub_nav_id ),
						/* Translators: "switch off" means to temporarily log out */
						'title'  => esc_html( $sub_nav_tab ),
						'href'   => esc_url( $sub_nav_link ),
					) );
				}
			}
		}
	}
}
