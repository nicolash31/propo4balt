<?php
$archivage_ok=1;
// GESTION ARCHIVE PARTIE 1 : fichier suivi (inscription/desinscription)
//---------------------------------------
$fichier_in="suivi.txt";
$fichier_out="archives/suivi_sem_".$sem."_a_".$cur_sem.".txt";
if (($idfile_in = fopen($fichier_in, "r")) and ($idfile_out= fopen($fichier_out, "w")))
{
	while ($ligne = fgets($idfile_in))
	{ fwrite($idfile_out,$ligne); }
	fclose($idfile_in);
	fclose($idfile_out);
}
else
{ $archivage_ok=0; }
// GESTION ARCHIVE PARTIE 2 : fichier visiteur
$fichier_in="visiteurs.txt";
$fichier_out="archives/visiteurs_sem_".$sem."_a_".$cur_sem.".txt";
if (($idfile_in = fopen($fichier_in, "r")) and ($idfile_out= fopen($fichier_out, "w")))
{
	while ($ligne = fgets($idfile_in))
	{ fwrite($idfile_out,$ligne); }
	fclose($idfile_in);
	fclose($idfile_out);
}
else
{ $archivage_ok=0; }
if ($monfichier = fopen("nouveau.txt", "w"))
{ 
	fwrite($monfichier,"Semaine = ".$cur_sem.CHR(10));
	fwrite($monfichier,"Nombre = ".$nb_new.CHR(10));
	fclose($monfichier);
}
else
{ $archivage_ok=0; }

if ($archivage_ok==1) //si tout c est bien passe on remet a 0 les anciens
{
	$fichier_out="suivi.txt";
	if ($idfile_out= fopen($fichier_out, "w"))
	{fclose($idfile_out);}
	$fichier_out="visiteurs.txt";
	if ($idfile_out= fopen($fichier_out, "w"))
	{fclose($idfile_out);}	
}
// GESTION ARCHIVE PARTIE 3
$monfichier = fopen('parametres.txt', 'r');
while ($ligne = fgets($monfichier))
	{
	if (preg_match("/^(\S+)\s*=\s*(\S+)\s*.*$/", $ligne,$matches))
		{ $param[$matches[1]]=$matches[2]; }
	}
fclose($monfichier); 
/// liste des noms de joueurs pour remplir le tableau des participants
$resultat=mysql_query("SELECT id,pseudo,statut FROM badistes" );
while ($ligne = mysql_fetch_array ($resultat))
    {      
	$noms_j[$ligne['id']]=$ligne['pseudo'];      
	$statut_j[$ligne['id']]=$ligne['statut'];      
	}
//on recherche les jours qui limite les semaines a traiter :
//du premier jour $sem au premier jour de $cur_sem
//pour cela on remonte les jours et on garde le dernier jour de la 
//bonne semaine dans les 2 cas
$jour   =date("d");
$mois   =date("m");
$annee  =date("Y");
for ($r=0;$r<200;$r++)
	{	
	$le_temps =mktime( 0, 0, 1, $mois, ($jour-$r), $annee );
	$la_date  =date("Y-m-d",$le_temps);
	$le_mois  =date("W",$le_temps);
	if ($le_mois==$cur_sem)
	{ $date_lim_h = $la_date; }
	if ($le_mois==$sem)
	{ $date_lim_b = $la_date; }
	}
$fichier_out="archives/tableau_sem_".$sem."_a_".$cur_sem.".html";
	if ($idfile_out= fopen($fichier_out, "w"))
	{
	$tab2write='<!DOCTYPE html><html lang="fr">
	<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Tableau recapitulatif semaine '.$sem.' a '.$cur_sem.'</title>
	<style>
	td { font-size : 12px;  font-family : Verdana, arial, helvetica, sans-serif;
         border-radius: 5px;  border: 4px solid #CCCCCC;  background-color : #FFFFFF;
		 padding: 0px 0px; }
	.table-pleine {  font-size : 12px;  font-family : Verdana, arial, helvetica, sans-serif;
		border-radius: 10px; border: 4px solid #99C343; text-align : center;
		background-color : #A9D353; }
	.table-paspleine { font-size : 12px; font-family : Verdana, arial, helvetica, sans-serif;
		border-radius: 10px; border: 4px solid #DD9966; text-align : center; 
		background-color : #FFBB88; }
	.table-ferme {  font-size : 12px;   font-family : Verdana, arial, helvetica, sans-serif;
		border-radius: 10px; border: 4px solid #666666; text-align : center;
		background-color : #888888; }
	.table-titre { font-size : 12px; font-family : Verdana, arial, helvetica, sans-serif;
		border-radius: 10px; border: 2px solid #BBBBBB; text-align : right;
		background-color : #DDDDDD; }
	.table-resa { border-radius: 5px; background-color: #FFFFFF; border: 2px #CCCCCC;
	    border-collapse: separate; border-spacing: 0px 0px; padding: 0px 0px; }
	</style>
	</head>
	<body>
	';
	$tab2write.="\n<h1><i>Tableau entre le ".date2str($date_lim_b,0)." et le ".date2str($date_lim_h,0)."</i></h1><br/>\n";
	$resultat=mysql_query("SELECT * FROM `date2bad` WHERE `date` >= '$date_lim_b' and `date` < '$date_lim_h' ORDER BY `date` ASC");
	$nb_jour=0; // nombre de jours trouves sur la periode
	while($data = mysql_fetch_assoc($resultat))
		{
		for ($c=1;$c<=2;$c++)
			{
			$coul[$nb_jour."_".$c]=' class="table-ferme"'; // par defaut ferme (sera remplace si non)
			if ($data['C'.$c]==1)
				{
				$participants[$nb_jour."_".$c]=""; // initiailisation de la liste des participants
				$nb_part[$nb_jour."_".$c]=0;
				$nb_invit[$nb_jour."_".$c]=0;
				// recherche des reservations a cette date et creneau
				$resultat2=mysql_query("SELECT *  FROM `resa` WHERE `date` = '".$data['date']."' AND `creneau` = ".$c);
				while($data2 = mysql_fetch_assoc($resultat2))
					{
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
				if ($nb_part[$nb_jour."_".$c]<$param["nb_places"]) /// ce n est pas plein :(
					{ $coul[$nb_jour."_".$c]=' class="table-paspleine"'; }
				else /// si c est plein c est bon signe => en vert :)
					{ $coul[$nb_jour."_".$c]=' class="table-pleine"'; }
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

	$tab2write.='<table class="table-resa"><tr>
	<td> Légende : </td>
	<td class="table-pleine" width="150px">jour complet</td>
	<td class="table-paspleine" width="150px">il restait des places</td>
	<td class="table-ferme" width="150px">fermé</td></tr></table></br></br>';
		
	$tab2write.="\n".'<table class="table-resa"><tr><td class="table-titre">Jour</td>';
	for ($j=0;$j<$nb_jour;$j++)
		{ $tab2write.="<td colspan=2 align=\"center\">".date2str($jour2jeu[$j],2)."</td>"; }
	$tab2write.="\n".'</tr><tr><td class="table-titre">Créneau</td>';	
	for ($j=0;$j<$nb_jour;$j++)
		{ $tab2write.='<td'.$coul[$j."_1"].'>19H->20H30</td><td'.$coul[$j."_2"].'>20H30->22H</td>'; }
	
	$tab2write.="\n".'</tr><tr><td class="table-titre">Nombre </br>de joueurs</td>';	
	for ($j=0;$j<$nb_jour;$j++)
		{ 
		$tab2write.='<td align="center"'.$coul[$j."_1"].'>'.$nb_part[$j."_1"]." / ".$param["nb_places"].'</td>
		<td align="center"'.$coul[$j."_2"].'>'.$nb_part[$j."_2"].' / '.$param["nb_places"]."</td>";
		}
	
	$tab2write.="\n".'</tr><tr><td class="table-titre">Nombre</br>d\'invités</td>';	
	for ($j=0;$j<$nb_jour;$j++)
		{
		for ($c=1;$c<=2;$c++)
			{ $tab2write.='<td align="center">'.$nb_invit[$j."_".$c].'</td>'; }
		}
	
	$tab2write.="\n".'</tr><tr valign="top" ><td class="table-titre">Joueurs</td>';	
	for ($j=0;$j<$nb_jour;$j++)
		{
		for ($c=1;$c<=2;$c++)
			{		$tab2write.='<td>'.$participants[$j."_".$c].'</br></td>';		}
		}
	$tab2write.="\n</tr></table>";
	fwrite($idfile_out,$tab2write); 
	fclose($idfile_out);
	}
// FIN de la partie archivage
?>