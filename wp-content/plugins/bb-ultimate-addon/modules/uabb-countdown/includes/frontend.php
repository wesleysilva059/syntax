<?php
/**
 *  UABB Countdown Module front-end file
 *
 *  @package UABB Countdown Module
 */

?>

<div class="uabb-module-content uabb-countdown">
	<div class="uabb-module-content uabb-countdown uabb-timer">
		<div id="countdown-<?php echo esc_attr( $module->node ); ?>" class="uabb-countdown uabb-countdown-<?php echo esc_attr( $settings->timer_type ); ?>-timer"></div>
	</div>
</div>
