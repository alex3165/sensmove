<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpressproj');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         't=*O14i[Dm+hc>jE)g4,_QnY}I L?D,C]+}+R-:pnK%YqkzpwrG|P`rePpcK*Z~|');
define('SECURE_AUTH_KEY',  '?y!zWo(#l@?c4C-oUTE@KBi+`n& _^+u)%v3h?$.5|i)dC`1igVRj}Vo>.|U{M,V');
define('LOGGED_IN_KEY',    'w/&n/k&9PLeiX7U&-kaNyEK2Kq:y.=|s$_kz|i ,ezE/;}bVE>i5;7l.aP;xRl@6');
define('NONCE_KEY',        'fm`[,8s|+7p_e` Q64CKOBfd!L&6Q1.:DR Q/$D|?x2mY!{<2tCQeiIYAx-!WTQq');
define('AUTH_SALT',        'jfR=+awdH{5k/A8v3[#jVvFhfu.)cm8*G$BqC-y!$<PFe-OGAX@Fh>f #le{T}Ge');
define('SECURE_AUTH_SALT', '.nv_/6]kQ|kT,!zU(Lrrr>W=,LtN@[lf~gK,sM=2(hD.L-YX;*4-l:>PSq CH4pR');
define('LOGGED_IN_SALT',   'Ia&^M_&=8X67j-3$;ggaOTt V^_.CVgac 8ftJ @!-8=-swAlPB$O}X+Qu+Y20sQ');
define('NONCE_SALT',       'o=|+WJ14?R]+Yg@qi2-_hq{.D@9+rW6WQbfo4v3LR3T|Ofq$pv;h,CtYT)Gn!7.z');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
