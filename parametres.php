<?php  
$page.="<h1><i>Paramètres</i></h1>\n";
if ($_SESSION["statut"]!=2)
	{
	$page.='<br/><div id="warning">Oups vous ne devriez pas avoir acces à cette page :( </div><br/><br/>'."\n";
	}	
else
{

if ($_POST["param_num"]>0)
	{
	if ($monfichier = fopen("parametres.txt", "w"))
		{
		foreach ($_POST as $par_name => $par_val)
			{ 	
			if (($par_name != "param_num") and (!(preg_match("/^.*_com$/", $par_name,$matches))))
				{fwrite($monfichier,$par_name." = ".$par_val." * ".$_POST[$par_name."_com"].CHR(10)); }
			}
		fclose($monfichier);
		}
	else
		{ $page.='<br/><div id="warning">impossible d ouvrir le fichier</div><br/><br/>'."\n"; }
	}

$page.='<form action="index.php?page=param" method="post">
		<table border="1"><tr>
		<td class="table-titre-center">paramètre</td>
		<td class="table-titre-center">valeur</td>
		<td class="table-titre-center">commentaire</td></tr>';
$monfichier = fopen('parametres.txt', 'r');
$nb_param=0;
while ($ligne = fgets($monfichier))
	{
	if (preg_match("/^(\S+)\s*=\s*(\S+)\s*\*\s*(.*)\s*$/", $ligne,$matches))
		{
		$param_name=$matches[1];
		$param_val=$matches[2];
		$param_com=$matches[3];
		$nb_param++;
		$page.='
		<tr><td class="table-titre">'.$param_name.'</td><td><input type="number" name="'.$param_name.'" value='.$param_val.' min="0" max="100"></td>
		<td><input type="hidden" name="'.$param_name.'_com" value="'.$param_com.'">
		'.$param_com.'</td></tr>';
		}
	}

$page.='<tr><td colspan=3 align="center"><input type="submit" value="Modifier" ></td></tr></table>
<input type="hidden" name="param_num" value='.$nb_param.'></form>';	
// 3 : quand on a fini de l'utiliser, on ferme le fichier
fclose($monfichier); 
}
?>