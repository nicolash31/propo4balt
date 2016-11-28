<?php  
function report($quoi, $qui) // fonction qui va ecrire dans le fichier de suivi
// les inscriptions ou desinscriptions
{	
$fichier="suivi.txt";
if ($id = fopen("$fichier", "a "))
	{
	$aecrire=$quoi." le ".date("Y-m-d")." a ".date("h:i:s")." pour ".$qui.CHR(10);
	fwrite($id,$aecrire);
	fclose($id);
	}
return true;
}

//extraction des parametres depuis le fichier parametres.txt
/*
on trouve pour chaque statut (admin, membre et invité) :
-l anticipation de la reservation (exemple : 2 <=> la personne peut reserver 2 jours avant)
-le nombre de créneau reservable sur la duree de reservation
*/

$monfichier = fopen('parametres.txt', 'r');
while ($ligne = fgets($monfichier))
	{
	if (preg_match("/^(\S+)\s*=\s*(\S+)\s*.*$/", $ligne,$matches))
		{ $param[$matches[1]]=$matches[2]; }
	}
fclose($monfichier); 

// recuperation de la date du jour
$jour=date("d");
$mois=date("m");
$annee=date("Y");
$today=date("Y-m-d");
$maintenant = mktime( 0, 0, 0, $mois, $jour, $annee );

// declaration de la personne en visiteur si elle n est pas reconnue
$visiteur=1;
if (isset($_SESSION["id_joueur"]))
	{ $visiteur=0; }

$j2res=0;
$nb2res=0;
$idj=$_SESSION["id_joueur"];
if ($_SESSION["statut"]==2)
	{ //membre admin
	$j2res=$param["nb_j_admin"];
	$nb2res=$param["nb_c_admin"];
	}
elseif ($_SESSION["statut"]==1)
	{ //simple membre
	$j2res=$param["nb_j_membre"];
	$nb2res=$param["nb_c_membre"];
	}
if ($_SESSION["statut"]==0)
	{ //membre invite
	$j2res=$param["nb_j_invite"];
	$nb2res=$param["nb_c_invite"];
	}
$jour2visu=$param["jour2visu"];
///-------FIN de la lecture des parametres-------------------
/// liste des noms de joueurs pour remplir le tableau des participants
$resultat=mysql_query("SELECT id,pseudo,statut FROM badistes" );
while ($ligne = mysql_fetch_array ($resultat))
    {      
	$noms_j[$ligne['id']]=$ligne['pseudo'];      
	$statut_j[$ligne['id']]=$ligne['statut'];      
	}
// cas d'une inscription
if (isset($_POST["date"]) and isset($_POST["creneau"]))
	{
	$invit=0;	$nom_invit="";
	if (($_POST["invit"]==1) and (strlen($_POST["nom_invit"])>0))
		{ $invit=1;		$nom_invit=$_POST["nom_invit"]; }
	// verification que le creneau n'a pas été reservé entre l'affichage de la page et le clic
	$verif_nb_inscrit=0;
	$resultat=mysql_query("SELECT ext FROM `resa` WHERE `date` = '".$_POST["date"]."' AND `creneau` ='".$_POST["creneau"]."' ");
	while($data = mysql_fetch_assoc($resultat))
    { $verif_nb_inscrit+=1+$data['ext']; }
	//
	if (($verif_nb_inscrit+1+$invit) > $param["nb_places"])
		{ $page.= '</br><div id="warning">Oooohhh Plus assez de place :( </div></br>'; }
	else
		{
		$requete="INSERT INTO `resa` (`id_j` ,`date` ,`creneau` ,`ext` ,`nom`)";
		$requete.="VALUES ( '$idj', '".$_POST["date"]."', '".$_POST["creneau"]."', '$invit', '$nom_invit');";
		if ($resultat=mysql_query($requete))
			{
			$page.= "\n".'</br><div id="noteok">inscription validée</div></br>'."\n";
			report("++ Inscription    ".$_POST["date"]." C".$_POST["creneau"],$noms_j[$idj]);
			}
		}
	}
	// cas d'une desinscription
elseif (isset($_POST["desinscri"]))
	{ 
	$resultat=mysql_query("SELECT id_j,date,creneau FROM resa WHERE `id_res` = ".$_POST["desinscri"]." LIMIT 1" );// on retrouve la resa
	$ligne = mysql_fetch_array ($resultat); // on retrouve la resa
	$requete="DELETE FROM `resa` WHERE `id_res` = ".$_POST["desinscri"]." LIMIT 1";
	if ($resultat=mysql_query($requete))
		{$page.= "\n".'</br><div id="noteok">Desinscription validée</div></br>'."\n";
		// pour pouvoir identifier les desinscriptions tardives	
		$temps2res =mktime( 0, 0, 0, substr($ligne['date'],5,2), substr($ligne['date'],8,2), substr($ligne['date'],0,4) );
		$dif2jour=floor(($temps2res-$maintenant)/86400);
		if ($dif2jour<=$param["jour2warm"])
			{ $warning="!! "; }
		else
			{ $warning="-- "; }
		report($warning."Desinscription ".$ligne['date']." C".$ligne['creneau'],$noms_j[$ligne['id_j']]);
		}
	}
	// cas d un ajout d invite
elseif (isset ($_POST["ajoutinvit"]))
	{
	$new_nom_invit=$_POST["nom_invit"];
	$resultat=mysql_query("SELECT * FROM resa WHERE `id_res` = ".$_POST["ajoutinvit"]." LIMIT 1" );// on retrouve la resa
	$ligne = mysql_fetch_array ($resultat); // on retrouve la resa
	if ($ligne['ext']>0) // si il y avait deja un invite
		{ $new_list_invit=$ligne['nom']."|||".$new_nom_invit; }
	else
		{ $new_list_invit=$new_nom_invit; }
	$new_num_invit=$ligne['ext']+1;
	// verification que le creneau n'a pas été reservé entre l'affichage de la page et le clic
	$verif_nb_inscrit=0;
	$resultat2=mysql_query("SELECT ext FROM `resa` WHERE `date` = '".$ligne["date"]."' AND `creneau` ='".$ligne["creneau"]."'");
	while($data = mysql_fetch_assoc($resultat2))
		{ $verif_nb_inscrit+=1+$data['ext']; }
	//
	if (($verif_nb_inscrit+1) > $param["nb_places"])
		{ $page.= '</br><div id="warning">Oooohhh Plus assez de place :( </div></br>'; }
	elseif (strlen($new_nom_invit)==0)
		{ $page.= '</br><div id="warning">Le nom de l\'invité ne doit pas etre vide :p </div></br>'; }
	else
		{	

		$requete="UPDATE `resa` SET `ext` = '".$new_num_invit."', `nom` = '".$new_list_invit."'  WHERE `id_res` =".$_POST["ajoutinvit"];
		if ($resultat=mysql_query($requete))
			{ 
			$page.= "\n".'</br><div id="noteok">ajout de l\'invité(e) '.$new_nom_invit.' validée</div></br>'."\n"; 
			report($warning."++ Ajout invite   ".$ligne['date']." C".$ligne['creneau'],$new_nom_invit." par ".$noms_j[$ligne['id_j']]);
			}
		}
	}
	// cas d une suppression invite
elseif (isset ($_GET["desinvit"]))
	{
	$resultat=mysql_query("SELECT * FROM resa WHERE `id_res` = ".$_GET["desinvit"]." LIMIT 1" );// on retrouve la resa
	$ligne = mysql_fetch_array ($resultat); // on retrouve la resa
	$tab_invit = preg_split("/\|\|\|/", $ligne['nom']);
	$new_list_invit="";
	$new_num_invit=0;
	for ($i=0;$i<count($tab_invit);$i++)
		{
		if ($i!=$_GET["num"]) 
			{
			$new_list_invit.=$tab_invit[$i]."|||";
			$new_num_invit++;
			}
		else
			{
			$old_nom_invit=$tab_invit[$i];	
			}
		}
	$new_list_invit=substr($new_list_invit,0,-3);
	$requete="UPDATE `resa` SET `ext` = '".$new_num_invit."', `nom` = '".$new_list_invit."'  WHERE `id_res` =".$_GET["desinvit"];
	if ($resultat=mysql_query($requete))
		{ 
		$page.= "\n".'</br><div id="noteok">Desinscription de l\'invité(e) '.$old_nom_invit.' validée</div></br>'."\n";
		// pour pouvoir identifier les desinscriptions tardives	
		$temps2res =mktime( 0, 0, 0, substr($ligne['date'],5,2), substr($ligne['date'],8,2), substr($ligne['date'],0,4) );
		$dif2jour=floor(($temps2res-$maintenant)/86400);
		if ($dif2jour<=$param["jour2warm"])
			{ $warning="!! "; }
		else
			{ $warning="-- "; }
		report($warning."supprime invit ".$ligne['date']." C".$ligne['creneau'],$old_nom_invit." par ".$noms_j[$ligne['id_j']]); 
		}
	}

//recherche dates jouables dans les dates visibles
$temps_visible =mktime( 0, 0, 0, $mois, ($jour+$jour2visu), $annee );
$date_visible  =date("Y-m-d",$temps_visible);
$page.="\n<h1><i>Réservation de créneaux</br>entre le ".date2str($today,0)." et le ".date2str($date_visible,0)."</i></h1><br/>\n";
$resultat=mysql_query("SELECT * FROM `date2bad` WHERE `date` >= '$today' and `date` <= '$date_visible' ORDER BY `date` ASC");
$nb_jour=0; // nombre de jours trouves sur la periode
$nb_resa=0; // nombre de reservations du joueur connecte
while($data = mysql_fetch_assoc($resultat))
    {
	$temps2res =mktime( 0, 0, 0, substr($data['date'],5,2), substr($data['date'],8,2), substr($data['date'],0,4) );
	$dif2jour=floor(($temps2res-$maintenant)/86400); // nombre de jour avant la date
	for ($c=1;$c<=2;$c++)
		{
		$coul[$nb_jour."_".$c]=' class="table-ferme"'; // par defaut ferme (sera remplace si non)
		$reservable[$nb_jour."_".$c]=0;
		if ($data['C'.$c]==1)
			{
			if ($dif2jour<=$j2res)
				{ $reservable[$nb_jour."_".$c]=1; } // on autorise la reservation
			$participants[$nb_jour."_".$c]=""; // initiailisation de la liste des participants
			$nb_part[$nb_jour."_".$c]=0;
			$nb_invit[$nb_jour."_".$c]=0;
			$inscrit[$nb_jour."_".$c]=0;
			// recherche des reservations a cette date et creneau
			$resultat2=mysql_query("SELECT *  FROM `resa` WHERE `date` = '".$data['date']."' AND `creneau` = ".$c);
			while($data2 = mysql_fetch_assoc($resultat2))
				{
				if ($data2['id_j']==$idj)
					{ 
					$nb_resa++; 
					$inscrit[$nb_jour."_".$c]=$data2['id_res'];
					}
				$participants[$nb_jour."_".$c].=$noms_j[$data2['id_j']]."<br/>";
				$nb_part[$nb_jour."_".$c]++;
				if ($statut_j[$data2['id_j']]==0) // si c'est un invité on ajoute 
					{ $nb_invit[$nb_jour."_".$c]++; }
				if ($data2['ext']>0) // si le joueur a au moins un invite
					{
					$tab_invit = preg_split("/\|\|\|/", $data2['nom']);
					for ($i=0;$i<count($tab_invit);$i++)
						{
						$enleve_invit="";
						if ($data2['id_j']==$idj)
							{ $enleve_invit='<a href=index.php?desinvit='.$data2['id_res'].'&num='.$i.'><img src="images/red_cro.gif"></a>'; }
						$participants[$nb_jour."_".$c].="(+i) ".$tab_invit[$i]." ".$enleve_invit.'<br/>';
						$nb_part[$nb_jour."_".$c]++;
						$nb_invit[$nb_jour."_".$c]++;
						}
					}
				}
			// on ajuste la couleur en fonction de la date et des places restantes	
			if ($nb_part[$nb_jour."_".$c]<$param["nb_places"])
				{ 
				if ($reservable[$nb_jour."_".$c] == 0)
					{ $coul[$nb_jour."_".$c]=' class="table-troploin"'; }
				else
					{ $coul[$nb_jour."_".$c]=' class="table-ouvert"'; }
				}
			else
				{ $coul[$nb_jour."_".$c]=' class="table-plein"'; }
			}
		}
	
	if ($data['C1']==1 or $data['C2']==1)
		{
		$jour2jeu[$nb_jour]=$data['date'];
		$C1jour[$nb_jour]=$data['C1'];
		$C2jour[$nb_jour]=$data['C2'];
		$nb_jour++;
		}
	}
$page.="\nVous avez	".$nb_resa." reservation(s) / ".$nb2res."(MAX)</br></br>\n";

// Affichage de la légende
$page.='<table class="table-resa"><tr>
<td> Légende : </td>
<td class="table-ouvert" width="150px">ouvert à la reservation</td>
<td class="table-troploin" width="150px">pas encore ouvert</td>
<td class="table-plein" width="150px">complet</td>
<td class="table-ferme" width="150px">fermé</td></tr></table></br></br>';

// Affichage de la table des reservations
$page.="\n".'<table class="table-resa"><tr><td class="table-titre">Jour</td>';
for ($j=0;$j<$nb_jour;$j++)
	{ $page.="<td colspan=2 align=\"center\">".date2str($jour2jeu[$j],2)."</td>"; }
$page.="\n".'</tr><tr><td class="table-titre">Créneau</td>';	
for ($j=0;$j<$nb_jour;$j++)
	{ $page.='<td'.$coul[$j."_1"].'>19H->20H30</td><td'.$coul[$j."_2"].'>20H30->22H</td>'; }

$page.="\n".'</tr><tr><td class="table-titre">Nombre </br>de joueurs</td>';	
for ($j=0;$j<$nb_jour;$j++)
	{ 
	$page.='<td align="center">'.$nb_part[$j."_1"]." / ".$param["nb_places"].'</td>
	<td align="center">'.$nb_part[$j."_2"].' / '.$param["nb_places"]."</td>";
	}
// pour les admin on affiche aussi une ligne avec le nombre d'invités
if ($_SESSION["statut"]==2) 
	{
	$page.="\n".'</tr><tr><td class="table-titre">Nombre</br>d\'invités</td>';	
	for ($j=0;$j<$nb_jour;$j++)
		{
		for ($c=1;$c<=2;$c++)
			{		$page.='<td align="center">'.$nb_invit[$j."_".$c].'</td>';		}
		}
	}
// partie inscription/desinscription du tableau
$page.="\n".'</tr><tr><td class="table-titre">Inscription</td>';	
for ($j=0;$j<$nb_jour;$j++)
	{
	for ($c=1;$c<=2;$c++)
		{
		if (($inscrit[$j."_".$c]>0) and ($visiteur==0)) // on est deja inscrit
			{//desinscription
			$page.='<td class="table-ouvert">
			<form action="index.php" method="post">
			<input type="hidden" name="desinscri" value="'.$inscrit[$j."_".$c].'">
			<input type="submit" value="Desinscription" ></form>';
			//ajout invite
			if (($nb_part[$j."_".$c]<($param["nb_places"])) and ($_SESSION["statut"]>=1))//il faut 1 place pour ajouter un invite et etre membre ou admin
				{
				$page.='<form action="index.php" method="post">
				<input type="hidden" name="ajoutinvit" value="'.$inscrit[$j."_".$c].'">
				<input type="text" placeholder="nom de l\'invité" name="nom_invit" size="15"></br>
				<input type="submit" value="Ajout Invité" ></form>';		
				}
			$page.='</td>';		
			}
		// si on est pas inscrit si il reste de la place et si on est pas simple visiteur
		elseif (($reservable[$j."_".$c]==1) and ($nb_resa<$nb2res) and ($nb_part[$j."_".$c]<$param["nb_places"]) and ($visiteur==0))
			{
			$page.='<td class="table-ouvert"><form action="index.php" method="post">';	
			// si on est pas soit meme un invité et si il reste de la place (il faut 2 places pour joueur et invité)
			// alors on autorise aussi un invité
			if (($nb_part[$j."_".$c]<($param["nb_places"]-1)) and ($_SESSION["statut"]>=1))
				{ // et un invité ne peut pas inviter
				$page.='avec invité <INPUT type="checkbox" name="invit" value="1"></br>
				<input type="text" placeholder="nom de l\'invité" name="nom_invit" size="15"></br>';	
				}
			else
				{ $page.='<input type="hidden" name="invit" value="0">
			              <input type="hidden" name="nom_invit" value=" ">'; }
			$page.='<input type="hidden" name="date" value="'.$jour2jeu[$j].'">
			<input type="hidden" name="creneau" value="'.$c.'">
			<input type="submit" value="Inscription">
			</form></td>';				
			}
		elseif (($reservable[$j."_".$c]==1) and ($nb_resa<$nb2res) and ($nb_part[$j."_".$c]=$param["nb_places"]) and ($visiteur==0))
			{$page.='<td class="table-plein">Complet</td>';}
		else	
			{$page.='<td class="table-ferme"></td>';}
		}
	}
//ligne des participants dans le tableau
$page.="\n".'</tr><tr valign="top" ><td class="table-titre">Joueurs</td>';	
for ($j=0;$j<$nb_jour;$j++)
	{
	for ($c=1;$c<=2;$c++)
		{ $page.='<td>'.$participants[$j."_".$c].'</br></td>'; }
	}
$page.="\n</tr></table>";	
?>