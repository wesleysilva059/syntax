<?php
/**
 * As configurações básicas do WordPress
 *
 * O script de criação wp-config.php usa esse arquivo durante a instalação.
 * Você não precisa usar o site, você pode copiar este arquivo
 * para "wp-config.php" e preencher os valores.
 *
 * Este arquivo contém as seguintes configurações:
 *
 * * Configurações do MySQL
 * * Chaves secretas
 * * Prefixo do banco de dados
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Configurações do MySQL - Você pode pegar estas informações com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define( 'DB_NAME', 'syntax' );

/** Usuário do banco de dados MySQL */
define( 'DB_USER', 'root' );

/** Senha do banco de dados MySQL */
define( 'DB_PASSWORD', '' );

/** Nome do host do MySQL */
define( 'DB_HOST', 'localhost' );

/** Charset do banco de dados a ser usado na criação das tabelas. */
define( 'DB_CHARSET', 'utf8mb4' );

/** O tipo de Collate do banco de dados. Não altere isso se tiver dúvidas. */
define( 'DB_COLLATE', '' );

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las
 * usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org
 * secret-key service}
 * Você pode alterá-las a qualquer momento para invalidar quaisquer
 * cookies existentes. Isto irá forçar todos os
 * usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'ivX;;4=+->Ig]Z$}mnLivF34dA#_}3p^P4(wX*8m#/bF+U( ^Q}mS@4q fjN;I)c' );
define( 'SECURE_AUTH_KEY',  '(]C=l>9Bn&L0[<+QJr6~(M?EzEa#7Urv>%:r^>e9R!H,[XYA,NDWJY)~|`M0}ec.' );
define( 'LOGGED_IN_KEY',    '~~&xo]T)!h@w>tRIG,FYVGLchmOB/R=1aASzJc=>qn>e_YaJM LQ/SX$vV0/nR53' );
define( 'NONCE_KEY',        '%)@h$aY|qGd{n38Ad2Wu70){[}h)GI&a`%XtApc%0G6u+8(;nP`iG@6v$xl&@/jc' );
define( 'AUTH_SALT',        '<<ZV)_&yh^d}q(R5dPc8bk`R@QNp8H&kGV]7hHwN}4#4ed-&E6`@Ga/Ba-0=i%y;' );
define( 'SECURE_AUTH_SALT', '$*:|vHvdz;]N0a|~c0wa1:+J5cfLSVCRh=/z4T~WvNv%q3H:U@9bP7awF*cyD{.9' );
define( 'LOGGED_IN_SALT',   'oFt6A+}em]1#m9_IG<A_U4gR/BcrBLaXLR* tF<dEkQbpcSxgf|36NiyBn@KS]@r' );
define( 'NONCE_SALT',       '$nAg1W66*@N=KBQdc(Vp7e_ioe=lDel9jzv*9bQS7~nr~XAc(%hH5%!,UY=i8g)*' );

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der
 * um prefixo único para cada um. Somente números, letras e sublinhados!
 */
$table_prefix = 'wp_';

/**
 * Para desenvolvedores: Modo de debug do WordPress.
 *
 * Altere isto para true para ativar a exibição de avisos
 * durante o desenvolvimento. É altamente recomendável que os
 * desenvolvedores de plugins e temas usem o WP_DEBUG
 * em seus ambientes de desenvolvimento.
 *
 * Para informações sobre outras constantes que podem ser utilizadas
 * para depuração, visite o Codex.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Isto é tudo, pode parar de editar! :) */

/** Caminho absoluto para o diretório WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Configura as variáveis e arquivos do WordPress. */
require_once ABSPATH . 'wp-settings.php';
