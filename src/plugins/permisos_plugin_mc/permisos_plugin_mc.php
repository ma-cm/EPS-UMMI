<?php
/*
Plugin Name: Mi plugin de funciones
*/


// Añadir permisos de lectura a páginas y entradas privadas para cualquier usuario con rol suscriptor.

function wp_acceso_contenido_privado()
{
global $wp_roles;
// Obtenemos en una variable el rol suscriptor.
$role = get_role('subscriber');
// Le añadimos el permiso de lectura a páginas privadas.
$role->add_cap('read_private_pages');
// Incluimos también el permiso de lectura a posts privados.
$role->add_cap('read_private_posts');
}

// Llamada a nuestra función.
add_action ( 'admin_init', 'wp_acceso_contenido_privado' );