
<?php
// si on arrive sur la page apres avoir rempli le formulaire
if (isset($_POST['pseudo']))
	{
		//recuperation des valeurs
	$id=$_POST['id'];
	$pseudo=$_POST['pseudo'];
	$mail_addr=$_POST['mail_addr'];
	$mot2passe=$_POST['mot2passe'];
	$statut=$_POST['statut'];
	// verification
	$resultat=mysql_query("SELECT id,pseudo,mail_addr FROM badistes WHERE `id`!='$id' and `pseudo`='$pseudo'" );
	$resultat2=mysql_query("SELECT id,pseudo,mail_addr FROM badistes WHERE `id`!='$id' and `mail_addr`='$mail_addr'" );
    if ($ligne=mysql_fetch_array($resultat))
        { $page.= '</br><div id="warning">Ce nom est deja utilisé</div></br>';}
    elseif ($ligne=mysql_fetch_array($resultat2))
        { $page.= '</br><div id="warning">Ce mail est deja utilisé</div></br>';}
    elseif (empty($mot2passe))  // on change pas le mot de passe
        {
        $requete="UPDATE badistes SET `pseudo`='$pseudo',`mail_addr`='$mail_addr',`statut`='$statut'  WHERE `id`='$id'";
        if ($resultat=mysql_query($requete,$base_id))
			{
			$page.= "\n".'</br><div id="noteok">Les informations ont été modifiées (sans modification du mot de passe)</div></br>'."\n";
			if ($id==$_SESSION['id_joueur']) // si la modif concerne l utilisateur on modifie les variables de session
				{  
				$_SESSION["nom"]=$pseudo;
				$_SESSION["statut"]=$statut;
				}
			}
        }
    else // on change le mot de passe
        {
        $md5_mdp=md5($mot2passe);
        $requete="UPDATE badistes SET `pseudo`='$pseudo',`mail_addr`='$mail_addr',`mot2passe`='$md5_mdp', `statut`='$statut'  WHERE `id`='$id'";
        if ($resultat=mysql_query($requete,$base_id))
			{
			$page.= "\n".'</br><div id="noteok">Les informations ont été modifiées (modification du mot de passe)</div></br>'."\n";
			if ($id==$_SESSION['id_joueur']) // si la modif concerne l utilisateur on modifie les variables de session
				{  
				$_SESSION["nom"]=$pseudo;
				$_SESSION["statut"]=$statut;
				}
			}
        }
	}

$page.="<h1>Information du joueur</h1>\n";
$page.="<i>Mot de passe vide => mot de passe inchangé</i>\n";
	// formulaire de modification de joueur
$onlyou="WHERE `id` = ".$_SESSION['id_joueur']." "; 
if ($_SESSION['statut'] == 2)
	{ $onlyou=""; }
$requete="SELECT id,pseudo,mail_addr,statut FROM badistes ".$onlyou;
$resultat=mysql_query($requete);
$page.= '<table border="1"><tr><td class="table-titre-center">pseudo</td><td class="table-titre-center">mail</td>
<td class="table-titre-center">statut</td><td class="table-titre-center">nouveau</br>mot de passe</td><td class="table-titre-center">Modifier</td></tr>';
while($data = mysql_fetch_assoc($resultat))
    {
    // on affiche les informations de l'enregistrement en cours
    $page.= '<tr><form action="index.php?page=joueur" method="post">
	<input type="hidden" name="id" value="'.$data['id'].'">
	<td>';
	if ($_SESSION['statut'] == 2) //on autorise admin a changer le nom
		{ $page.= '<input type="text" size="30" name="pseudo" value="'.$data['pseudo'].'">'; }
	else
		{ $page.= $data['pseudo'].'
		<input type="hidden" name="pseudo" value="'.$data['pseudo'].'">'; }
	$page.= '</td>
	<td><input type="text" size="50" name="mail_addr" value="'.$data['mail_addr'].'"></td>
	<td>';
////------------parte portant sur le statut------------------------------------------	
	if ($_SESSION['statut'] == 2) //on autorise admin a changer le statut des autres
		{ 
		$page.= '<select name="statut"><option value="0"';    
		if ($data['statut']==0) { $page.= ' selected="selected" ';}
		$page.= ' >invite</option>
		<option value="1"';    
		if ($data['statut']==1) { $page.= ' selected="selected" ';}
		$page.= '>membre</option>
		<option value="2"';    
		if ($data['statut']==2) { $page.= ' selected="selected" ';}
		$page.= '>admin</option>
		</select>';
		}
	else
		{ 
		if     ($data['statut']==1) { $page.= 'Membre';}
		elseif ($data['statut']==2) { $page.= 'Admin';}
		else                        { $page.= 'invite';}
		}
	$page.= '</td>
	<td><input type="password" size="30" name="mot2passe" value=""></td>
	<td><input type="submit" value="MODIFIER"></td></tr>
	</form>';
    }
$page.= "</table>";
////**********************************************
?>
