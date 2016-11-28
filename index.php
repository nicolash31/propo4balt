<?php
header( 'content-type: text/html; charset=utf-8' );
function date2str($madate, $type) // fonction qui va retourner la date en littéral
{	//type 0 : 04 janvier
	//type 1 : 04 janvier 2016
	//type 2 : mardi 08 janvier
	$strdate=substr($madate,8,2)." ";	
	$mois=substr($madate,5,2);
	switch ($mois) {
		case 1:		$strdate.="janvier"; break;
		case 2:		$strdate.="février"; break;
		case 3:		$strdate.="mars"; break;
		case 4:		$strdate.="avril"; break;
		case 5:		$strdate.="mai"; break;
		case 6:		$strdate.="juin"; break;
		case 7:		$strdate.="juillet"; break;
		case 8:		$strdate.="aout"; break;
		case 9:		$strdate.="septembre"; break;
		case 10:	$strdate.="octobre"; break;
		case 11:	$strdate.="novembre"; break;
		case 12:	$strdate.="décembre"; break;
		default:	$strdate.="";
		}
	
	if ($type==1)
		{ $strdate.=" ".substr($madate,0,4); }
	if ($type==2)
		{
		$moment =mktime( 0, 0, 0, $mois, substr($madate,8,2), substr($madate,0,4) );
		$jour=(date("w",$moment)+6)%7; // 0=lundi 1=mardi...
		switch ($jour) {
			case 0:		$strdate="lundi ".$strdate; break;
			case 1:		$strdate="mardi ".$strdate; break;
			case 2:		$strdate="mercredi ".$strdate; break;
			case 3:		$strdate="jeudi ".$strdate; break;
			case 4:		$strdate="vendredi ".$strdate; break;
			case 5:		$strdate="samedi ".$strdate; break;
			case 6:		$strdate="dimanche ".$strdate; break;
			default:	$strdate=$strdate;
			}
		}
    return $strdate;
}

session_start();
$page="";
include('connect_base.php');
$base_id=mysql_connect($hote,$utilisateur,$motpasse) or die("La connexion à la base de données a échouée");
mysql_select_db($nom_base,$base_id) or die( "La sélection de la base a échoué");

if (isset($_SESSION["nom"]) or $_GET["log"]=="logout")
	{ 
	//
	}
// Si le formulaire de login a été rempli, on va le vérifier dans la base
elseif (isset($_POST["nom"]))      
	{
	$resultat=mysql_query("SELECT * FROM `badistes`");
	while ($ligne = mysql_fetch_array ($resultat))
		{    $noms_j[$ligne['idjoueur']]=$ligne['pseudo'];    }
	$requete="SELECT * FROM badistes WHERE pseudo ='".$_POST["nom"]."'";
	$resultat=mysql_query($requete);
	if($ligne = mysql_fetch_array ($resultat))
		{
		if ($ligne['mot2passe']==md5($_POST['passe']))
			{
			$_SESSION["nom"] = $ligne['pseudo'];
			$_SESSION["id_joueur"]= $ligne['id'];
			$_SESSION["statut"]= $ligne['statut'];
			setcookie('balt_cook',$ligne['pseudo'].$ligne['mot2passe'],time()+3600*24*15); // cookie valable 15 jours
			$fichier="visiteurs.txt";
			if ($id = fopen("$fichier", "a "))
				{
				$aecrire="Login ".date("Y-m-d")." a ".date("H:i:s")." ".$ligne['pseudo'].CHR(10);
				fwrite($id,$aecrire);
				fclose($id);
				}
			}
		else
			{ $page.='<br/><div id="warning">Mot de passe incorrect</div><br/><br/>'."\n"; }
		}
	else
		{ $page.='<br/><div id="warning">Utilisateur inconnu</div><br/><br/>'."\n"; }
	}
// Sinon on recherche dans les cookies
elseif (isset($_COOKIE["balt_cook"]))
	{ 
	$name2find=substr($_COOKIE["balt_cook"],0,-32);
	$mdp2find=substr($_COOKIE["balt_cook"],-32,32);
	$requete="SELECT * FROM badistes WHERE mot2passe ='".$mdp2find."' and pseudo ='".$name2find."'";
	$resultat=mysql_query($requete);
	if($ligne = mysql_fetch_array ($resultat))
		{
		$_SESSION["nom"] = $ligne['pseudo'];
		$_SESSION["id_joueur"]= $ligne['id'];
		$_SESSION["statut"]= $ligne['statut'];
		$fichier="visiteurs.txt";
		if ($id = fopen("$fichier", "a "))
			{
			$aecrire="cook  ".date("Y-m-d")." a ".date("H:i:s")." ".$ligne['pseudo'].CHR(10);
			fwrite($id,$aecrire);
			fclose($id);
			}
		}	
	}
/*
gestion de l archivage on utilise le fichier qui permet de limiter le nombre 
d inscription par semaine pour detecter le debut d une nouvelle semaine et lancer 
l archivage
*/
$monfichier = fopen('nouveau.txt', 'r');
while ($ligne = fgets($monfichier))
	{
	if (preg_match("/^Semaine\s*=\s*(\d+)\s*$/", $ligne,$matches))
		{ $sem=$matches[1]; }
	elseif (preg_match("/^Nombre\s*=\s*(\d+)\s*$/", $ligne,$matches))
		{ $nb_new=$matches[1]; }
	}
fclose($monfichier); 
$cur_sem=date("W");
if ($cur_sem != $sem)
	{ include('creation_archives.php'); }
// FIN de la partie archivage	
include('feuilles.php');
include($lapage);
mysql_close($base_id);	
?>
<html lang="fr">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>proposition de planning BALT</title>
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="icon" type="image/ico" href="/images/favicon.ico" />
</head>
<body>

<?php
if     ($_SESSION["statut"]==0) { $kicki="invité"; }
elseif ($_SESSION["statut"]==1) { $kicki="membre"; }
elseif ($_SESSION["statut"]==2) { $kicki="admin"; }
else                            { $kicki="visiteur"; }
echo '<table border="0" width=100%><tr><td  class="noborder_left"><a href="index.php"><img src="images/maison_balt.jpg" ></a></td><td   class="noborder_right">';
if (isset($_SESSION["nom"]))
	{ 
	echo 'Bonjour <a href="index.php?page=joueur">'.$_SESSION["nom"].'</a>('.$kicki.')</br>
	<a href="index.php?page=login&log=logout">se deconnecter</a>'; 
	if ($_SESSION["statut"]==2)
		{ echo '
<ul id=menu>
admin options
<li><a href="index.php?page=param">paramètres</a>
<li><a href="index.php?page=archives">archives</a>
<li><a href="index.php?page=date">gestion des dates</a>
<li><a href="index.php?page=ajou">nouveau joueur</a>
<li><a href="index.php?page=joueur">gestion joueurs</a>
<li><a href="index.php?page=inscrilist">inscription groupee</a>
</ul>'; }
	}
else
	{ 
	echo '<div style="text-align: right"><a href="index.php?page=login">se connecter</a></br>
	<a href="index.php?page=ajou">creer un compte invité</a></div>';
	}
echo "</td></tr></table></br>\n";
echo $page;
?>
</body>
</html>