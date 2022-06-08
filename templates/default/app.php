<?php
/**
 * The template for the local Application
 *
 * @package Incassoos
 * @subpackage Application
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">

<?php incassoos_app_head(); ?>
</head>

<body <?php body_class(); ?>>
	<div id="root"></div>

<?php incassoos_app_footer(); ?>
</body>
</html>
