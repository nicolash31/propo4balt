<?php 
$statut=0; //valeur par defaut
///
$monfichier = fopen('parametres.txt', 'r');
while ($ligne = fgets($monfichier))
	{
	if (preg_match("/^(\S+)\s*=\s*(\S+)\s*.*$/", $ligne,$matches))
		{ $param[$matches[1]]=$matches[2]; }
	}
fclose($monfichier); 

$form_ko=1; // pour permettre le preremplir le formulaire en cas de mauvaise saisie a corriger

// si un formulaire a été saisi
if (isset($_POST['pseudo']))
{	
	$pseudo=$_POST['pseudo'];
	$mail_addr=$_POST['mail_addr'];
	$mot2passe=$_POST['mot2passe'];
	$statut=$_POST['statut'];
	if (preg_match("#(.+)@(.+)\.(.+)#", $mail_addr,$matches))
		{
		$resultat=mysql_query("SELECT pseudo FROM badistes WHERE `pseudo`='$pseudo'"); 
		$resultat2=mysql_query("SELECT pseudo FROM badistes WHERE `mail_addr`='$mail_addr'");
		if ($ligne=mysql_fetch_array($resultat))
			{ $page.='<div id="warning">Ce nom est deja utilise</div><br />'; }
		elseif ($ligne=mysql_fetch_array($resultat2))
			{ $page.='<div id="warning">Cette adresse mail est deja utilisee</div><br />';  }
		elseif (strlen($mot2passe)==0)
			{ $page.='<div id="warning">mot de passe vide</div><br />';   }	
		else // on change le mot de passe
			{
			$md5_mdp=md5($mot2passe);
			$requete="INSERT INTO badistes (`id` ,`pseudo` ,`mail_addr` ,`mot2passe` ,`statut`) VALUES (NULL, '$pseudo', '$mail_addr', '$md5_mdp', '$statut')";
			if ($resultat=mysql_query($requete,$base_id))
				{ 
				$form_ko=0;
				$page.='<div id="noteok">Inscription du nouveau joueur réussie</div><br />'; 
				if ($cur_sem != $sem)
					{ $nb_new=0; }
				if ($_SESSION['statut'] != 2) //seul les nouveaux ajoutés par admin ne comptent pas
					{ $nb_new++; }
				if ($monfichier = fopen("nouveau.txt", "w"))
					{ 
					fwrite($monfichier,"Semaine = ".$cur_sem.CHR(10));
					fwrite($monfichier,"Nombre = ".$nb_new.CHR(10));
					fclose($monfichier);
					}
				}
			}
		}
	else
		{ $page.='<div id="warning">Format de l adresse mail incorrect</div></br>'; }	
	}
if (($param["visit_sem"]<=$nb_new) and ($cur_sem == $sem) and ($_SESSION['statut'] != 2) and ($form_ko==1))
	{ $page.='<div id="warning">Plus d inscription autorisée cette semaine</div><br />'; }
elseif 	(($_SESSION['statut'] == 2) or ($form_ko==1))
	{
	$page.='
	<form action="" method="post">
	<table border="1"><tr>
	<tr><td colspan="2" class="table-titre-center">Ajout d un joueur</td></tr>
	<tr><td class="table-titre">nom : </td><td><input type="text" size="30" name="pseudo" value="'.$pseudo.'"></td></tr>
	<tr><td class="table-titre">adresse mail : </td><td><input type="text" size="30" name="mail_addr" value="'.$mail_addr.'"></td></tr>
	<tr><td class="table-titre">mot2passe : </td><td><input type="password" size="30" name="mot2passe" value="'.$mot2passe.'"></td></tr>';
	if ($_SESSION['statut'] == 2) //on autorise admin a changer le statut des autres
		{ 
		$page.=' <tr><td class="table-titre">statut : </td><td><select name="statut"> <option value="0"';   
		if ($statut==0) { $page.=' selected="selected" ';}
		$page.=' >invite</option> <option value="1"';    
		if ($statut==1) { $page.=' selected="selected" ';}
		$page.='>membre</option> <option value="2"';
		if ($statut==2) { $page.=' selected="selected" ';}
		$page.='>membre admin</option>
		</select></td></tr>';
		}
	else
		{ $page.= '<input type="hidden" name="statut" value="0">'; }
	$page.='	
	<tr><td colspan="2"><input type="submit" value="AJOUTER"></td></tr></table>
	</form>
	';
	}
?>