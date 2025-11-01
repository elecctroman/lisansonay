<?php
namespace CodexPro;

/**
 * Simple PSR-4 autoloader for the plugin.
 */
class Autoloader {
    /**
     * Registers autoloader.
     */
    public static function register(): void {
        spl_autoload_register( [ __CLASS__, 'autoload' ] );
    }

    /**
     * Autoload callback.
     *
     * @param string $class Class name.
     */
    public static function autoload( string $class ): void {
        if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
            return;
        }

        $relative = substr( $class, strlen( __NAMESPACE__ . '\\' ) );
        $relative = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );
        $file     = plugin_dir_path( CODEX_PRO_PLUGIN_FILE ) . 'src/' . $relative . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
}
