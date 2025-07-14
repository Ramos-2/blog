  # 🚀 Guide de Connexion - Blog Groupe 4

## ✅ Configuration Terminée

Votre blog est maintenant entièrement configuré et prêt à être utilisé !

## 🔐 Identifiants de Connexion

### 👑 Utilisateur Administrateur
- **Nom d'utilisateur :** `admin`
- **Email :** `admin@blog.com`
- **Mot de passe :** `admin123`
- **Rôle :** Administrateur
- **Accès :** Panel d'administration complet

### 👤 Utilisateur Normal
- **Nom d'utilisateur :** `jean.dupont`
- **Email :** `jean.dupont@example.com`
- **Mot de passe :** `user123`
- **Rôle :** Utilisateur
- **Accès :** Dashboard utilisateur

## 🌐 Pages d'Accès

### Page de Connexion
```
http://localhost/blog%20groupe%204/pages/login.php
```

### Dashboard Utilisateur
```
http://localhost/blog%20groupe%204/pages/dashboard.php
```

### Panel d'Administration
```
http://localhost/blog%20groupe%204/admin/dashboard.php
```

## 📋 Fonctionnalités Disponibles

### Pour l'Utilisateur Normal (jean.dupont)
- ✅ Connexion au dashboard utilisateur
- ✅ Gestion du profil personnel
- ✅ Navigation avec sidebar
- ✅ Accès aux articles et commentaires

### Pour l'Administrateur (admin)
- ✅ Connexion au panel d'administration
- ✅ Gestion complète des utilisateurs
- ✅ Gestion des articles et catégories
- ✅ Modération des commentaires
- ✅ Paramètres du blog
- ✅ Statistiques et analytics

## 🛠️ Structure du Projet

```
blog groupe 4/
├── admin/                 # Panel d'administration
│   ├── dashboard.php     # Dashboard admin
│   ├── users.php         # Gestion utilisateurs
│   ├── articles.php      # Gestion articles
│   ├── comments.php      # Modération commentaires
│   └── settings.php      # Paramètres
├── pages/                # Pages utilisateur
│   ├── login.php         # Page de connexion
│   ├── register.php      # Page d'inscription
│   ├── dashboard.php     # Dashboard utilisateur
│   └── index.php         # Page d'accueil
├── actions/              # Actions de traitement
│   ├── login_action.php  # Traitement connexion
│   ├── register_action.php # Traitement inscription
│   └── logout_action.php # Déconnexion
├── includes/             # Fichiers inclus
│   ├── header.php        # En-tête commun
│   ├── footer.php        # Pied de page
│   └── sidebar.php       # Barre latérale
└── assets/               # Ressources
    └── styles/           # Fichiers CSS
```

## 🔧 Base de Données

- **Nom :** `blog_forum`
- **Tables créées :**
  - `users` - Utilisateurs
  - `articles` - Articles
  - `categories` - Catégories
  - `comments` - Commentaires
  - `tags` - Tags
  - `settings` - Paramètres
  - `media` - Médias

