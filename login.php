<?php
$n=0;
$resultat=mysql_query("SELECT pseudo,mail_addr FROM badistes");
while($data = mysql_fetch_assoc($resultat))
	{
	$list_j[$n]=$data['pseudo'];
	$n++;
	}
sort($list_j);			
$page.='<form action="index.php" method="post">
<table align="center">
<tr><td colspan="2" align="center">Identifiez vous :</td></tr>
<tr><td class="table-titre">nom : </td><td>
<select name="nom">';
foreach ($list_j as $lenom)
      {$page.='<option value="'.$lenom.'">'.$lenom.'</option>'; }
$page.='</select></td>
</tr>
<tr><td class="table-titre"> mot de passe : </td><td><input type="password" name="passe" size="20"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="Identifier" ></td></tr>
</table></form>';

if(isset($_GET["log"]) && $_GET["log"]=="logout")   // Si l'user a cliqué sur logout, on le déconnecte
  {
  unset($_SESSION["nom"]);
  unset($_SESSION["id_joueur"]);
  unset($_SESSION["statut"]);
  session_destroy();
  setcookie('balt_cook');
} 
?>
