<?php

/**
 * Email_Encoder_Helpers Class
 *
 * This class contains all of the available helper functions
 *
 * @since 2.0.0
 */

/**
 * The helpers of the plugin.
 *
 * @since 2.0.0
 * @package EEB
 * @author Ironikus <info@ironikus.com>
 */
class Email_Encoder_Helpers {

	/**
	 * Checks if the parsed param is available on the current site
	 *
	 * @param $param
	 * @return bool
	 */
	public function is_page( $param ){
		if( empty( $param ) ){
			return false;
		}

		if( isset( $_GET['page'] ) ){
			if( $_GET['page'] == $param ){
				return true;
			}
		}

		return false;
	}

	/**
	 * Creates a formatted admin notice
	 *
	 * @param $content - notice content
	 * @param string $type - Status of the specified notice
	 * @param bool $is_dismissible - If the message should be dismissible
	 * @return string - The formatted admin notice
	 */
	public function create_admin_notice($content, $type = 'info', $is_dismissible = true){
		if(empty($content))
			return '';

		/**
		 * Block an admin notice based onn the specified values
		 */
		$throwit = apply_filters('eeb/helpers/throw_admin_notice', true, $content, $type, $is_dismissible);
		if(!$throwit)
			return '';

		if($is_dismissible !== true){
			$isit = '';
		} else {
			$isit = 'is-dismissible';
		}


		switch($type){
			case 'info':
				$notice = 'notice-info';
				break;
			case 'success':
				$notice = 'notice-success';
				break;
			case 'warning':
				$notice = 'notice-warning';
				break;
			case 'error':
				$notice = 'notice-error';
				break;
			default:
				$notice = 'notice-info';
				break;
		}

		if( is_array( $content ) ){
			$validated_content = sprintf( __( $content[0], 'email-encoder-bundle' ), $content[1] );
        } else {
			$validated_content = __( $content, 'email-encoder-bundle' );
        }

		ob_start();
		?>
		<div class="notice <?php echo $notice; ?> <?php echo $isit; ?>">
			<p><?php echo $validated_content; ?></p>
		</div>
		<?php
		$res = ob_get_clean();

		return $res;
	}

	/**
	 * Formats a specific date to datetime
	 *
	 * @param $date
	 * @return DateTime
	 */
	public function get_datetime($date){
		$date_new = date('Y-m-d H:i:s', strtotime($date));
		$date_new_formatted = new DateTime($date_new);

		return $date_new_formatted;
	}

	/**
	 * Builds an url out of the mai values
	 *
	 * @param $url - the default url to set the params to
	 * @param $args - the available args
	 * @return string - the url
	 */
	public function built_url( $url, $args ){
		if(!empty($args)){
			$url .= '?' . http_build_query($args);
		}

		return $url;
	}

	/**
	 * Get Parameters from URL string
	 *
	 * @param $url - the url
	 *
	 * @return array - the parameters of the url
	 */
	public function get_parameters_from_url( $url ){

		$parts = parse_url($url);

		parse_str($parts['query'], $url_parameter);

		return empty( $url_parameter ) ? array() : $url_parameter;

	}

	/**
	 * Builds an url out of the main values
	 *
	 * @param $url - the default url to set the params to
	 * @param $args - the available args
	 * @return string - the url
	 */
	public function get_current_url($with_args = true){

		$current_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? 'https://' : 'http://';

		$host_part = $_SERVER['SERVER_NAME'];
		if( strpos( $host_part, $_SERVER['HTTP_HOST'] ) === false ){

		    //Validate against HTTP_HOST in case SERVER_NAME has no "www" set
			if( strpos( $_SERVER['HTTP_HOST'], '://www.' ) !== false && strpos( $host_part, '://www.' ) === false ){
				$host_part = str_replace( '://', '://www.', $host_part );
			}

		}

		$current_url .= sanitize_text_field( $host_part ) . sanitize_text_field( $_SERVER['REQUEST_URI'] );

	    if($with_args){
	        return $current_url;
        } else {
	        return strtok( $current_url, '?' );
        }
	}

	/**
     * This is the opponent of JavaScripts decodeURIComponent()
     * @link http://stackoverflow.com/questions/1734250/what-is-the-equivalent-of-javascripts-encodeuricomponent-in-php
     * @param string $str
     * @return string
     */
    public function encode_uri_components( $content ) {
        $revert = array( '%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')' );
        return strtr( rawurlencode( $content ), $revert );
	}
	
	/**
	 * Generate a random bool value
	 *
	 * @return bool
	 */
	public function get_random_bool(){
		return ( rand(0,1) == 1 ) ? true : false;
	}
	
}
