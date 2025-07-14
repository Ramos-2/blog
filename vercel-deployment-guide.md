# 🚀 Guide de déploiement Vercel - Blog Groupe 4

## 🎯 **Avantages de Vercel**

- ✅ **Support PHP** (Serverless Functions)
- ✅ **Base de données** (Vercel Postgres, Vercel KV)
- ✅ **Déploiement automatique** depuis GitHub
- ✅ **Performance optimisée**
- ✅ **SSL gratuit**
- ✅ **CDN global**

## 🔄 **Options de déploiement**

### **Option 1 : PHP avec Vercel (Recommandée)**

Vercel supporte PHP via des **Serverless Functions**, ce qui est parfait pour votre projet !

### **Option 2 : Conversion en Next.js/React**

Pour des performances optimales et plus de flexibilité.

### **Option 3 : Site statique avec API**

Garder l'interface en statique et utiliser des API pour les données.

## 🚀 **Option 1 : Déploiement PHP sur Vercel**

### **Étape 1 : Préparer le projet**

1. **Créer un fichier `vercel.json`** à la racine
2. **Adapter la structure pour Vercel**
3. **Configurer la base de données**

### **Étape 2 : Configuration Vercel**

#### **Fichier `vercel.json`**
```json
{
  "functions": {
    "api/*.php": {
      "runtime": "vercel-php@0.6.0"
    }
  },
  "routes": [
    {
      "src": "/api/(.*)",
      "dest": "/api/$1"
    },
    {
      "src": "/(.*)",
      "dest": "/public/$1"
    }
  ],
  "env": {
    "DB_HOST": "@db-host",
    "DB_NAME": "@db-name",
    "DB_USER": "@db-user",
    "DB_PASS": "@db-pass"
  }
}
```

### **Étape 3 : Structure adaptée**

```
blog-groupe-4/
├── api/                   # API PHP (Serverless Functions)
│   ├── auth.php          # Authentification
│   ├── articles.php      # Gestion articles
│   ├── comments.php      # Gestion commentaires
│   └── users.php         # Gestion utilisateurs
├── public/               # Fichiers statiques
│   ├── index.html        # Page d'accueil
│   ├── dashboard.html    # Dashboard
│   ├── css/             # Styles
│   ├── js/              # JavaScript
│   └── images/          # Images
├── includes/            # Classes PHP partagées
├── vercel.json          # Configuration Vercel
└── composer.json        # Dépendances PHP
```

## 🗄️ **Base de données sur Vercel**

### **Option A : Vercel Postgres (Recommandée)**
- ✅ **Gratuit :** 256MB de base de données
- ✅ **Intégré** à Vercel
- ✅ **Migration automatique** depuis MySQL

### **Option B : Vercel KV (Redis)**
- ✅ **Gratuit :** 100MB de stockage
- ✅ **Parfait** pour les sessions et cache

### **Option C : Services externes**
- **PlanetScale** (MySQL compatible)
- **Supabase** (PostgreSQL)
- **Neon** (PostgreSQL)

## 🔧 **Adaptation du code**

### **1. Connexion base de données**

**Avant (MySQL) :**
```php
$pdo = new PDO("mysql:host=localhost;dbname=blog_forum", "root", "");
```

**Après (PostgreSQL sur Vercel) :**
```php
$pdo = new PDO(
    "pgsql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS']
);
```

### **2. API Endpoints**

**Exemple : `api/articles.php`**
```php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/database.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Récupérer les articles
        $stmt = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
        break;
        
    case 'POST':
        // Créer un article
        $data = json_decode(file_get_contents('php://input'), true);
        // Logique de création
        break;
}
?>
```

## 🌐 **Option 2 : Next.js/React (Performance optimale)**

### **Avantages :**
- ⚡ **Performance maximale**
- 🔄 **Rendu côté serveur**
- 📱 **PWA ready**
- 🎨 **Interface moderne**

### **Structure Next.js :**
```
blog-groupe-4/
├── pages/                # Pages Next.js
│   ├── index.js         # Accueil
│   ├── dashboard.js     # Dashboard
│   ├── articles/        # Articles
│   └── api/            # API routes
├── components/          # Composants React
├── styles/             # CSS/SCSS
├── public/             # Assets statiques
└── package.json        # Dépendances
```

## 📋 **Fichiers de configuration**

### **vercel.json (PHP)**
```json
{
  "functions": {
    "api/*.php": {
      "runtime": "vercel-php@0.6.0"
    }
  },
  "routes": [
    {
      "src": "/api/(.*)",
      "dest": "/api/$1"
    },
    {
      "src": "/(.*\\.(css|js|png|jpg|jpeg|gif|ico|svg))",
      "dest": "/public/$1"
    },
    {
      "src": "/(.*)",
      "dest": "/public/index.html"
    }
  ]
}
```

### **composer.json (PHP)**
```json
{
  "require": {
    "php": ">=8.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "includes/"
    }
  }
}
```

### **package.json (Next.js)**
```json
{
  "name": "blog-groupe-4",
  "version": "1.0.0",
  "scripts": {
    "dev": "next dev",
    "build": "next build",
    "start": "next start"
  },
  "dependencies": {
    "next": "^13.0.0",
    "react": "^18.0.0",
    "react-dom": "^18.0.0"
  }
}
```

## 🚀 **Processus de déploiement**

### **Étape 1 : Préparer le repository**
1. **Pousser le code sur GitHub**
2. **Vérifier que tous les fichiers sont présents**
3. **Tester localement**

### **Étape 2 : Connexion Vercel**
1. **Aller sur [vercel.com](https://vercel.com)**
2. **Créer un compte** (connexion GitHub possible)
3. **Cliquer sur "New Project"**
4. **Importer votre repository**

### **Étape 3 : Configuration**
1. **Framework Preset :** PHP ou Next.js
2. **Root Directory :** (laisser vide)
3. **Build Command :** (automatique)
4. **Output Directory :** (automatique)

### **Étape 4 : Variables d'environnement**
Dans les paramètres du projet :
```
DB_HOST=votre-host-postgres
DB_NAME=votre-base
DB_USER=votre-utilisateur
DB_PASS=votre-mot-de-passe
```

## 🎯 **Recommandations par type de projet**

### **Pour votre blog actuel :**
1. **Option PHP** - Plus simple, moins de changements
2. **Vercel Postgres** - Migration facile depuis MySQL
3. **API Routes** - Garder la logique existante

### **Pour un nouveau projet :**
1. **Next.js** - Performance et flexibilité maximales
2. **Vercel Postgres** - Base de données intégrée
3. **TypeScript** - Code plus robuste

## 💡 **Avantages Vercel vs Netlify**

| Fonctionnalité | Vercel | Netlify |
|----------------|--------|---------|
| Support PHP | ✅ | ❌ |
| Base de données | ✅ | ❌ |
| Serverless Functions | ✅ | ✅ |
| Performance | ⚡ | ⚡ |
| Déploiement | 🚀 | 🚀 |
| SSL | ✅ | ✅ |

## 🔄 **Migration depuis votre projet actuel**

### **Étapes recommandées :**
1. **Garder la structure PHP** existante
2. **Créer les API endpoints** dans `/api/`
3. **Adapter les connexions** base de données
4. **Tester localement** avec Vercel CLI
5. **Déployer progressivement**

Voulez-vous que je vous aide à :
1. **Adapter votre code PHP** pour Vercel ?
2. **Créer la structure API** ?
3. **Configurer la base de données** Vercel Postgres ?
4. **Commencer la migration** étape par étape ? 