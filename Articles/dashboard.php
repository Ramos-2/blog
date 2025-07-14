<?php
$categories = isset($_POST['categories']) && count($_POST['categories']) > 0  // Vérifie si l'utilisateur a sélectionné des catégories depuis le formulaire
// Si oui, on les récupère dans categories ; sinon, on met "Informatique" par défaut
  ? $_POST['categories']
  : ['Informatique']; // (Par défaut si rien coché) 


function makeLink($cat) // Fonction qui génère un lien pour chaque catégorie
{
  $file = strtolower($cat) . '.php';
  return "<li><a href='$file'>$cat</a></li>";
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  </head>
<body>
  <h2>Bienvenue sur votre Dashboard</h2>
  <ul>
    <?php foreach ($categories as $cat) { echo makeLink($cat); } ?> <!-- Affiche une liste des liens pour chaque catégorie sélectionnée -->
  </ul>
</body>
</html>
