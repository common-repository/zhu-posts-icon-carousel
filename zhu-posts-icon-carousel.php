<?php
/**
 * ZHU Post Icon Carousel Plugin
 *
 * @package   ZHU-PIC\Main

 * @wordpress-plugin
 * Contributors: davidpullin
 * Plugin Name:  Zhu Posts Icon Carousel
 * Plugin URI:
 * Description:  Rotating carousel of icons of posts with optional preview below
 * Tags: posts, carousel, recent posts, scroller, widget
 * Version:  1.1.1
 * Stable Tag:   1.1.1
 * Requires at least:5.3.0
 * Tested up to: 5.8
 * Requires PHP: 7.0.0
 * Author:   David Pullin
 * Author URI:   https://ict-man.me
 * License:  GPL v2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.en.html
 * Text Domain:  zhu_pic_domain
 */

/*
 * Copyright (C) 2021  David Pullin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v2 or later
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/gpl-2.0.en.html>.
 */


if ( ! defined( 'ABSPATH' ) ) {
	header( 'Location: /' );
	exit;
}

// load widget class if file is present and queue initialization and registration.
if ( file_exists( __DIR__ . '/lib/class-zhu-pic-1-widget.php' ) ) {
	require_once __DIR__ . '/lib/class-zhu-pic-1-widget.php';

	add_action( 'widgets_init', 'zhu_pic_1_register_widget' );
	add_action( 'wp_enqueue_scripts', 'zhu_pic_1_enqueue_scripts' );
}

// are we in admin mode? If so, load additional support scripts.
if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'zhu_pic_enqueue_admin_scripts' );
}

function zhu_pic_1_register_widget() {
	register_widget( 'Zhu_Pic_1_Widget' );
}

function zhu_pic_1_enqueue_scripts() {
	wp_register_script( 'zhu_pic_1_js', plugins_url( 'zhu-posts-icon-carousel/js/zhu-pic-1.js' ), array( 'jquery' ), '1.0.0', false );
	wp_enqueue_script( 'zhu_pic_1_js' );
}

function zhu_pic_enqueue_admin_scripts() {

	// Localization used by zhu-posts-icon-carousel-admin.js of settings for CodeMirror.
	// https://wpreset.com/add-codemirror-editor-plugin-theme/
	// @requires_wordpress 4.9.0(when code editor was introduced).
	$cm_settings['codeEditor'] = wp_enqueue_code_editor(
		array(
			'type'		 => 'text/css',
			'codemirror' => array(
				'indentUnit'	 => 2,
				'tabSize'		 => 2,
				'lineNumbers'	 => true,
			),
		)
	);
	wp_localize_script( 'jquery', 'zhu_pic_cm_settings', $cm_settings );

	// make sure there are being enquque.
	wp_enqueue_script( 'wp-theme-plugin-editor' );
	wp_enqueue_style( 'wp-codemirror' );

	// Addidional CSS for widget administration.
	wp_register_style(
		'zhu-posts-icon-carousel-admin-css',
		plugins_url( 'zhu-posts-icon-carousel/css/zhu-posts-icon-carousel-admin.css' ),
		array(),
		'1.0.1'
	);
        
        // Are we using the widget block editor introduced into WP 5.8?
        if( function_exists('wp_use_widgets_block_editor') ) {
            $use_widgets_block_editor = wp_use_widgets_block_editor();
        }
        else {
            $use_widgets_block_editor = false;
        }

	// Localization used by zhu-posts-icon-carousel-admin.js to set message to display when validating numeric input ranges.
	$admin_settings = array(
		'value_too_low'	 => esc_attr__( 'Value too low', 'zhu_pic_domain' ),
		'value_too_high' => esc_attr__( 'Value too high', 'zhu_pic_domain' ),
		'must_be'		 => esc_attr__( 'must be', 'zhu_pic_domain' ),
                'use_widgets_block_editor' => $use_widgets_block_editor,
	);
	wp_localize_script( 'jquery', 'zhu_pic_admin', $admin_settings );

	// Load JavaScript required for administration of our Widget.  Used by zhu-posts-icon-carousel-admin.js.
	wp_register_script(
		'zhu-posts-icon-carousel-admin-js',
		plugins_url( 'zhu-posts-icon-carousel/js/zhu-posts-icon-carousel-admin.js' ),
		array( 'jquery' ),
		'1.0.1',
		false
	);

	wp_enqueue_style( 'zhu-posts-icon-carousel-admin-css' );
	wp_enqueue_script( 'zhu-posts-icon-carousel-admin-js' );
}
