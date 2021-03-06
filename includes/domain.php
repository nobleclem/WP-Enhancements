<?php

/* WPMU Domain Mapping Tweaks */
class WPEnhancements_Domain
{
    static public function init()
    {
        // actions/filters here
        add_filter( 'wp_get_nav_menu_items', array( __CLASS__, 'menu_items' ), 10 );

        add_filter( 'theme_mod_header_image', array( __CLASS__, 'string_url_fix' ) );
        add_filter( 'the_content', array( __CLASS__, 'string_url_fix' ) );

        add_filter( 'post_link', array( __CLASS__, 'string_url_fix' ), 99 );

        // admin bar url fixing
        add_action( 'wp_before_admin_bar_render', array( __CLASS__, 'admin_bar_render_start' ) );
        add_action( 'wp_after_admin_bar_render', array( __CLASS__, 'admin_bar_render_end' ) );
    }

    static public function string_url_fix( $string )
    {
        // just return the data if its not a string
        if( !is_string( $string ) ) {
            return $string;
        }
        
        if( defined( 'DOMAIN_MAPPING' ) && function_exists( 'get_original_url' ) ) {
            // normalize domain
            $string = str_replace(
                preg_replace( '#^https?://#i', '', get_original_url( 'siteurl' ) ),
                preg_replace( '#^https?://#i', '', get_option( 'siteurl' ) ),
                $string
            );
        }

        // normalize protocol for current request and domain
        $string = str_replace(
            'http'. (isset( $_SERVER['HTTPS'] ) ? null : 's') .'://'. preg_replace( '#^https?://#i', '', get_option( 'siteurl' ) ),
            'http'. (isset( $_SERVER['HTTPS'] ) ? 's' : null) .'://'. preg_replace( '#^https?://#i', '', get_option( 'siteurl' ) ),
            $string
        );

        return $string;
    }

    static public function menu_items( $items )
    {
        foreach( $items as &$item ) {
            $item->url  = self::string_url_fix( $item->url );
            $item->guid = self::string_url_fix( $item->guid );
        }

        return $items;
    }


    /* Fix multi-site admin bar urls */
    static public function admin_bar_render_start()
    {
        if( defined( 'DOMAIN_MAPPING' ) && function_exists( 'get_original_url' ) ) {
            ob_start();
        }
    }

    static public function admin_bar_render_end()
    {   
        if( defined( 'DOMAIN_MAPPING' ) && function_exists( 'get_original_url' ) ) {
            echo preg_replace(
                '#https?://'. preg_quote( preg_replace( '#^https?://#i', '', get_option( 'siteurl' ) ) ) .'#i',
                preg_replace( '#^https?://#i', 'http'. (FORCE_SSL_ADMIN ? 's' : '') .'://', get_original_url( 'siteurl' ) ),
                ob_get_clean()
            );
        }
    }
}
WPEnhancements_Domain::init();
