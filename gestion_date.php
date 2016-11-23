
<?php 

$page.='<h1>Gestion des créneaux reservables</h1></br>
';

if ($_SESSION["statut"]!=2)
	{
	$page.='<br/><div id="warning">Oups vous ne devriez pas avoir acces à cette page :( </div><br/><br/>'."\n";
	}	
else
{
$page.='<font color="green">en vert les créneaux reservables</font></br>
<font color="red">en rouge les créneaux non-reservables</font></br>
</br>
<table border="1">';
if (isset ($_GET["jour"])) {
$jour=$_GET["jour"];
$mois=intval(substr($jour,5,2));
$annee=intval(substr($jour,0,4));
$num_jour=intval(substr($jour,8,2));
} 
else
{
$mois=date("m");
$annee=date("Y");
$num_jour=(date("w")+6)%7;
}
$cur_mois=date("m");
$cur_annee=date("Y");
$cur_jour=date("d");

$temps_1m  =mktime( 0, 0, 0, $mois, 1, $annee ); // jour 1 du mois
$date_1m   =date("Y-m-d",$temps_1m);
$temps_p1m =mktime( 0, 0, 0, ($mois+1), 1, $annee ); // jour 1 du mois suivant
$date_p1m  =date("Y-m-d",$temps_p1m);
$temps_m1m =mktime( 0, 0, 0, ($mois-1), 1, $annee ); // jour 1 du mois precedent
$date_m1m  =date("Y-m-d",$temps_m1m);

for ($j=1;$j<=31;$j++)
	{ 	$C1sel[$j]=0; $C2sel[$j]=0;	$C1existe[$j]=0; $C2existe[$j]=0; 	}
//recherche des dates deja utilisees
$resultat=mysql_query("SELECT * FROM `date2bad` WHERE `date` >= '".$date_1m."' and `date` < '".$date_p1m."'");
while($data = mysql_fetch_assoc($resultat))
    {
	$j_tmp=intval(substr($data['date'],8,2));
	$C1sel[$j_tmp]=$data['C1'];  $C1existe[$j_tmp]=$data['id_date'];
	$C2sel[$j_tmp]=$data['C2'];  $C2existe[$j_tmp]=$data['id_date'];
    }
	
// recherche si une date a été cochée	
for ($j=1;$j<=31;$j++)
{ 
$jour=sprintf("%04.0f-%02.0f-%02.0f",$annee,$mois,$j);
if ($_GET["C1"]==1 and $num_jour==$j)
	{
	$C1sel[$j]=1-$C1sel[$j];
	if ($C1existe[$j]==0)
		{ $requete="INSERT INTO `date2bad` (`date`, `C1`, `C2`) VALUES ('".$jour."', '1', '0')"; }
	else
		{ $requete="UPDATE `date2bad` SET `C1` = '".$C1sel[$j]."' WHERE `id_date` =".$C1existe[$j]; }
	mysql_query($requete);
	}
if ($_GET["C2"]==1 and $num_jour==$j)
	{
	$C2sel[$j]=1-$C2sel[$j];
	if ($C2existe[$j]==0)
		{ $requete="INSERT INTO `date2bad` (`date`, `C1`, `C2`) VALUES ('".$jour."', '0', '1')"; }
	else
		{ $requete="UPDATE `date2bad` SET `C2` = '".$C2sel[$j]."' WHERE `id_date` =".$C2existe[$j]; }
	mysql_query($requete);
	}
}

$temps = mktime( 0, 0, 0, ($mois), 1, $annee ); 
$pjdm=(date("w",$temps)+6)%7; //premier jour du mois
$nbj4mois=date("t",$temps); //nombre de jour dans le mois
$page.= '<tr><td class="table-troploin"><a href=index.php?page=date&jour='.$date_m1m.'>prec</a></td>
<td colspan="5"  class="table-ouvert">';
$page.= date("F Y",$temps);
$page.= '</td><td class="table-troploin"><a href=index.php?page=date&jour='.$date_p1m.'>suiv</a></td></tr>';
$page.= '<tr>
<td class="table-ouvert">Lundi</td>
<td class="table-ouvert">Mardi</td>
<td class="table-ouvert">Mercredi</td>
<td class="table-ouvert">Jeudi</td>
<td class="table-ouvert">Venderdi</td>
<td class="table-ouvert">Samedi</td>
<td class="table-ouvert">Dimanche</td></tr>';

	$num_jour=1;
	while ($num_jour<=$nbj4mois)
	{
		$page.= "<tr>";
		for ($d=0;$d<7;$d++)
			{
			if ($num_jour<=$nbj4mois and ($num_jour>1 or $d==$pjdm))
				{
				$jour=sprintf("%04.0f-%02.0f-%02.0f",$annee,$mois,$num_jour);
				if ($C1sel[$num_jour]==1) {$colC1="green";} else {$colC1="red";}
				if ($C2sel[$num_jour]==1) {$colC2="green";} else {$colC2="red";}
				$page.= '<td align="center">';
				if (($num_jour==$cur_jour) and ($mois==$cur_mois) and ($annee==$cur_annee))
					{ $page.= '<b>'.$num_jour.'</b>'; }
				else
					{ $page.= $num_jour; }
				$page.= '</br>
				<a href=index.php?page=date&jour='.$jour.'&C1=1><font color="'.$colC1.'">19H00->20H30</font></a>
				</br>
				<a href=index.php?page=date&jour='.$jour.'&C2=1><font color="'.$colC2.'">20H30->22H00</font></a>
				</td>'; 
				$num_jour++;
				}
			else 
				{ $page.= '<td class="table-ferme">_</td>'; }
			}
	$page.= "</tr>";
	}	
$page.= '</table></br>';
} 
?>