<?php

namespace Netgen\Bundle\EzSyliusBundle\Util;

use Gedmo\Sluggable\Util\Urlizer as BaseUrlizer;

class Urlizer extends BaseUrlizer
{
    /**
     * Uses transliteration tables to convert any kind of utf8 character
     *
     * @param string $text
     * @param string $separator
     * @return string $text
     */
    public static function transliterate( $text, $separator = '-' )
    {
        if ( preg_match( '/[\x80-\xff]/', $text ) && self::validUtf8( $text ) )
        {
            $text = self::utf8ToAscii( $text );
        }

        return self::postProcessText( $text, $separator );
    }

    /**
     * Does not transliterate correctly eastern languages
     *
     * @param string $text
     * @param string $separator
     * @return string
     */
    public static function urlize( $text, $separator = '-' )
    {
        $text = self::unaccent( $text );
        return self::postProcessText( $text, $separator );
    }

    /**
     * Cleans up the text and adds separator
     *
     * @param string $text
     * @param string $separator
     * @return string
     */
    private static function postProcessText( $text, $separator )
    {
        if ( function_exists( 'mb_strtolower' ) )
        {
            $text = mb_strtolower( $text );
        }
        else
        {
            $text = strtolower( $text );
        }

        // Remove all non word characters - except slashes
        //$text = preg_replace( '/\W/', ' ', $text );
        $text = preg_replace( '/[^A-Za-z0-9\/]+/', ' ', $text );

        // More stripping. Replace spaces with dashes
        $text = strtolower(
            preg_replace(
                '/[^A-Za-z0-9\/]+/', $separator,
                preg_replace(
                    '/([a-z\d])([A-Z])/', '\1_\2',
                    preg_replace(
                        '/([A-Z]+)([A-Z][a-z])/', '\1_\2',
                        preg_replace( '/::/', '/', $text )
                    )
                )
            )
        );

        return trim( $text, $separator );
    }
}
