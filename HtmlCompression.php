/**
 * Minify HTML Output in WordPress
 *
 * @author: Pablo López Mestre - https://desarrollowp.com
 */
class HtmlCompression {
    public function __construct() {
        add_action( 'wp_loaded', self::html_compression_start() );
    }

    public static function html_compression_start() {
        ob_start( array( __CLASS__, 'html_compression_finish' ) );
    }

    public static function html_compression_finish( $html ) {
        $pattern = '/(?<js><script.*?<\/script\s*>)|(?<css><style.*?<\/style\s*>)|(?<html>(?:[^!\/\w.:-])?[^<]*)/ms';

        preg_match_all( $pattern, $html, $matches, PREG_SET_ORDER );

        // Variable reused for output
        $output = '';

        foreach ( $matches as $token ) {
            if ( ! empty( $token['js'] ) ) {
                $output .= self::minify_js( $token['js'] );
            } elseif ( ! empty( $token['css'] ) ) {
                $output .= self::minify_css( $token['css'] );
            } else {
                $output .= self::minify_html( $token['html'] );
            }
        }

        return $output ?: $html;
    }

    /**
     * Minify inline JS
     *
     * @param string $content All JS captured by regex.
     *
     * @return string
     */
    private static function minify_js( string $content = '' ): string {
        $pattern = array(
            '/(?<!ftp:|http:|https:|"|\')\s*\/\/[^\n\r]*/ms',
            // Remove JavaScript Inline comments (Don't remove if it's a URL)
            '/\/\*.*?\*\//ms',
            // Remove JavaScript Block comments
            '/[\n\r\t\v\e\f]/',
            // Remove all new lines, carriage returns, tabs, vertical whitespaces, esc & form feeds characters
            '/\s{2,}/s',
            // Remove all spaces (when there are 2 or more)
        );

        $replacement = array(
            '',
            '',
            '',
            ' ',
        );

        return preg_replace( $pattern, $replacement, $content );
    }

    /**
     * Minify inline CSS
     *
     * @param string $content All CSS captured by regex.
     *
     * @return string
     */
    private static function minify_css( string $content = '' ): string {
        $pattern = array(
            '/\/\*.*?\*\//ms',
            // Remove CSS Block comments
            '/[\n\r\t\v\e\f]/',
            // Remove all new lines, carriage returns, tabs, vertical whitespaces, esc & form feeds characters
            '/\s{2,}/s',
            // Remove all spaces (when there are 2 or more)
        );

        $replacement = array(
            '',
            '',
            '',
        );

        return preg_replace( $pattern, $replacement, $content );
    }

    /**
     * Minify rest of HTML
     *
     * @param string $content All HTML captured by regex.
     *
     * @return string
     */
    private static function minify_html( string $content = '' ): string {
        $pattern = array(
            '/<!--\s.*?-->/',
            // Remove all HTML comments
            '/[\n\r\t\v\e\f]/',
            // Remove all new lines, carriage returns, tabs, vertical whitespaces, esc & form feeds characters
            '/\s{2,}/',
            // Remove all spaces (when there are 2 or more)
        );

        $replacement = array(
            '',
            '',
            ' ',
        );

        return preg_replace( $pattern, $replacement, $content );
    }
}

$minify_html = new HtmlCompression();
