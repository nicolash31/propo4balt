<?php 
///// si  on arrive en ayant confirme le chargement
if (($_POST['confirme_load'] == 1) and ($_POST['num_ok'] > 0))
{
	$requete="";
	$monfichier = fopen("req_a_faire.txt", 'r');
	while ($ligne = fgets($monfichier))
	{ $requete.=$ligne; }
	fclose($monfichier);
	
	if ($resultat=mysql_query($requete,$base_id))
		{ $page.='<div id="noteok">Inscription réussie</div><br />'; }
	else
		{ $page.='<div id="warning">Echec de l Inscription</div><br />'; }
	if ($id = fopen("req_a_faire.txt", "w ")) //pour vider le fichier
		{ fclose($id); }
}
////

///// si  on arrive en ayant demander le chargement
// on va lire le fichier et tirer les infos
elseif ($_POST['envoi_fichier']==1)
{
$req_ok="";
$first=1;
$num_ok=0;
	$error=0;
	$extension_upload = strtolower(  substr(  strrchr($_FILES['mon_fichier']['name'], '.')  ,1)  );
	if ( $extension_upload != "csv" )
		{ 
		$error=1; 
		$page.='<br/><div id="warning">Erreur sur le type de fichier</div><br/>'."\n"; 
		}
	
	if (($_FILES['mon_fichier']['size']<=0) or ($_FILES['mon_fichier']['size']>1048576))
		{ 
		$error=1; 
		$page.='<br/><div id="warning">Erreur sur la taille du fichier</div><br/>'."\n"; 
		}
	
	if ($_FILES['mon_fichier']['error'] == 0 and ($error==0))
	{	
		$n=0;
		$resultat=mysql_query("SELECT pseudo,mail_addr FROM badistes");
		while($data = mysql_fetch_assoc($resultat))
			{
			$list_j[$n]=strtolower($data['pseudo']);
			$list_m[$n]=strtolower($data['mail_addr']);
			$n++;
			}
			
		$num_ligne=0;
		$page_recap="";
		$page_err="";
		$monfichier = fopen($_FILES['mon_fichier']['tmp_name'], 'r');
		while ($ligne = fgets($monfichier))
		{	
			$num_ligne++;
			$ligne=utf8_encode($ligne);
			$tab2champ=preg_split("/\;/",$ligne);
			if (count($tab2champ)>=4)
			{
				$nom_j=$tab2champ[0];
				$mail_j=$tab2champ[1];
				$mdp=$tab2champ[2];
				$statut=$tab2champ[3];
				$mdp_j=md5($mdp);
				if (preg_match("#admin#", $statut))
				{ $statut_j=2; }
				elseif (preg_match("#membre#", $statut))
				{ $statut_j=1; }
				else
				{ $statut_j=0; }
					
				if 	(in_array(strtolower($nom_j),$list_j))
				{ $page_err.="ligne ".$num_ligne." : le nom ".$nom_j." existe deja</br>\n"; }
				elseif 	(in_array(strtolower($mail_j),$list_m))
				{ $page_err.="ligne ".$num_ligne." : l'adresse mail ".$mail_j." existe deja</br>\n"; }
				elseif (preg_match("#(.+)@(.+)\.(.+)#", $mail_j,$matches))
				{	
					$page_recap.="ligne ".$num_ligne." : ajout de ".$nom_j." mail=".$mail_j." statut=".$statut_j." mdp=".$mdp."</br>\n";
					$num_ok++;
					if ($first==1)
					{ 
						$first=0;
						$req_ok.="INSERT INTO `badistes` (`pseudo`, `mail_addr`, `mot2passe`, `statut`) VALUES\n";
					}
					$req_ok.="('$nom_j', '$mail_j', '$mdp_j', '$statut_j'),\n";
				}
				else
				{ $page_err.="ligne ".$num_ligne." : format du mail ko</br>\n"; }
			}
			else
			{
				$page_err.="ligne ".$num_ligne." : pas assez de champ dans la ligne</br>\n";
			}
		}
		$page.='<table>
		<tr><td class="table-listko"><h3>lignes non-conformes</h3></br>'.$page_err.'</td></tr>
		<tr><td class="table-listok"><h3>lignes conformes qui seront appliquées</h3></br>'.$page_recap.'</td></tr>
		</table>';
		$req_ok=substr($req_ok,0,-2).";";
		fclose($monfichier);
		if ($id = fopen("req_a_faire.txt", "w "))
			{
			fwrite($id,$req_ok);
			fclose($id);
			}
		$page.= '
<form method="post" action="index.php?page=inscrilist">
<input type="hidden" name="confirme_load" value="1" />
<input type="hidden" name="num_ok" value="'.$num_ok.'" />
<input type="submit" name="submit" value="CONFIRMER LE CHARGEMENT" />
</form>'; 
	}
}
else
{
$page.='
<table>
<form method="post" action="index.php?page=inscrilist" enctype="multipart/form-data">
<tr><td class="table-titre-center"><label for="mon_fichier">Fichier csv (max. 1 Mo) </label></td></tr>
<input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
<input type="hidden" name="envoi_fichier" value="1" />
<tr><td><input type="file" name="mon_fichier" id="mon_fichier" /></td></tr>
<tr><td class="table-titre-center"><input type="submit" name="submit" value="Envoyer" /></td></tr>
</form></table>
</br>
Le fichier doit etre un fichier csv (par exemple exporté depuis excel)</br>
Le format des lignes attendu est le suivant:</br>
<i>Nom;adresse mail;mot de passe;statut</i></br>
le statut pouvant être "admin" ou "membre" ou "invité"</br>
=>Les lignes du fichier qui ne respecteront pas ce formalisme ne seront pas traitées</br>
=>Après le chargement et avant validation des nouvelles inscriptions 
la page detaillera les erreurs trouvées sur le format des lignes</br>
exemple : <a href="exemple_importation_joueurs.csv">exemple_importation_joueurs.csv</a>
';
}
?>