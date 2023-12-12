<?php
/*
Plugin Name: Filtrar Contenido con Frases Aleatorias y Título Dinámico
Description: Añade una frase aleatoria entre paréntesis después de la mención del número 33 en el contenido. Cambia el título según el día de la semana.
Version: 1.0
Author: Tu Nombre
*/

// Acción para activar el plugin
register_activation_hook( __FILE__, 'crear_tabla_frases_aleatorias' );

function crear_tabla_frases_aleatorias() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'frases_aleatorias';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $tabla (
        id INT PRIMARY KEY AUTO_INCREMENT,
        frase TEXT NOT NULL
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Insertar las frases aleatorias iniciales
    $frases_iniciales = array(
        '¿Cómo? A ver, repíteme ese numerín.',
        'Si, hoooombre.',
        '¿33? ¿Seguro?'
    );

    foreach ( $frases_iniciales as $frase ) {
        $wpdb->insert( $tabla, array( 'frase' => $frase ) );
    }
}

// Filtro para modificar el contenido
add_filter( 'the_content', 'filtrar_contenido_con_frases_aleatorias', 1 );

function filtrar_contenido_con_frases_aleatorias( $content ) {
    global $wpdb;

    // Busca la mención del número 33 y añade una frase aleatoria
    $content = preg_replace_callback( '/\b33\b/', function( $matches ) use ( $wpdb ) {
        $tabla = $wpdb->prefix . 'frases_aleatorias';
        $frase_aleatoria = $wpdb->get_var( "SELECT frase FROM $tabla ORDER BY RAND() LIMIT 1" );

        if ( $frase_aleatoria ) {
            return $matches[0] . ' (' . $frase_aleatoria . ')';
        }

        return $matches[0];
    }, $content );

    return $content;
}

// Filtro para wp_title
add_filter( 'wp_title', 'filtrar_wp_title', 10, 3 );

function filtrar_wp_title( $title, $sep, $seplocation ) {
    // Cambiar el título según el día de la semana
    $dias_semana = array(
        'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'
    );

    $titulo_dia = $dias_semana[gmdate( 'w' )]; // Obtiene el día de la semana actual

    // Agrega el título dinámico
    return $titulo_dia . $sep . $title;
}
