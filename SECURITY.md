# Politique de sécurité

Sàmm traite des signalements de personnes qui peuvent être en situation de vulnérabilité. Une faille de sécurité peut les exposer. Merci de la signaler de façon responsable.

## Signaler une faille

- Écrivez à **tourendeyeyande@gmail.com** en décrivant le problème, les étapes pour le reproduire et l'impact possible.
- N'ouvrez **pas** d'issue publique et ne publiez aucun détail avant la correction.
- Ne testez jamais une faille sur une instance Sàmm réelle : utilisez une installation locale avec des données fictives.

Nous accusons réception sous 5 jours ouvrés, nous vous tenons informé de l'analyse et de la correction, et nous vous créditons si vous le souhaitez.

## Périmètre

Sont concernés le code de ce dépôt et les configurations qu'il fournit. Les instances exploitées par des organisations tierces relèvent de leur propre politique.

## Versions prises en charge

Le projet est en phase de prototype : seule la branche principale reçoit des correctifs de sécurité.

## Bonnes pratiques pour les installations

- Servez Sàmm en HTTPS uniquement.
- Conservez `APP_KEY` et les sauvegardes de la base de données en lieu sûr : les champs sensibles sont chiffrés avec cette clé.
- N'exécutez jamais le seeder de démonstration (`DemoUsersSeeder`) sur une installation réelle.
- Activez l'authentification à deux facteurs pour les comptes de gestion.
