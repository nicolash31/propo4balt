<?php 
$nom_dossier="./archives";
$dossier = opendir($nom_dossier);
$suivi_arch_num=0;
$visit_arch_num=0;
while ($fichier = readdir($dossier))
	{
	if (preg_match("/^visiteurs_sem_.*\.txt$/", $fichier))
		{ $visit_tab[$visit_arch_num]=$fichier; $visit_arch_num++; }
	elseif (preg_match("/^suivi_sem_.*\.txt$/", $fichier))
		{ $suivi_tab[$suivi_arch_num]=$fichier; $suivi_arch_num++; }
	}
closedir($dossier);	
rsort($visit_tab);
rsort($suivi_tab);
$page.='<table><tr>
<td class="table-titre-center">fichiers de l\'historique des visites</td>
<td class="table-titre-center">fichiers de l\'historique des (des)inscriptions</td></tr>
<tr>
<td>courant : <a href="visiteurs.txt">visiteurs.txt</a></td>
<td>courant : <a href="suivi.txt">suivi.txt</a></td></tr>
<tr><td>';
for ($n=0;$n<$visit_arch_num;$n++)
{ $page.="\n".'<a href="archives/'.$visit_tab[$n].'">'.$visit_tab[$n].'</a></br>'; }
$page.='</td><td>';
for ($n=0;$n<$suivi_arch_num;$n++)
{ $page.="\n".'<a href="archives/'.$suivi_tab[$n].'">'.$suivi_tab[$n].'</a></br>'; }
$page.='</td></tr></table>';
?>