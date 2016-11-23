<?php
switch ($_REQUEST['page'])
{  
case 'joueur':
  $lapage='modif_joueur.php';
break;

case 'ajou':
  $lapage='ajout_joueur.php';
break;

case 'date';
  $lapage='gestion_date.php';
break;

case 'login';
  $lapage='login.php';
break;

case 'param';
  $lapage='parametres.php';
break;

case 'archives';
  $lapage='archives.php';
break;

case 'inscrilist';
  $lapage='inscri_liste.php';
break;

default :
  $lapage='reservation.php';
}
?>