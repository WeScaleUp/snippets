# WeScaleUp Manager

WordPress plugin die alle WeScaleUp standaardfunctionaliteit beheert voor klantwebsites.

## Functionaliteit

- **Beveiliging** — XML-RPC uitgeschakeld, REST API beperkt tot ingelogde gebruikers, WordPress versienummer verborgen
- **Admin branding** — WeScaleUp logo in de admin-balk, aangepaste footer, login-pagina huisstijl
- **SVG uploads** — toegestaan voor administrators
- **Reacties** — volledig uitgeschakeld en verborgen
- **Wachtwoordpagina** — gestylde WeScaleUp wachtwoordpagina
- **Dashboard** — aangepaste dashboard widget met WeScaleUp contactgegevens

## Installatie

Download de ZIP van de [nieuwste release](../../releases/latest) en upload via **Plugins → Nieuwe plugin → Uploaden** in WordPress.

## Updates uitrollen

1. Pas de code aan
2. Verhoog het versienummer in `wescaleup-manager.php`
3. Maak een nieuwe GitHub Release aan met tag `v1.x.x`
4. WordPress-sites ontvangen automatisch een update-melding

## Structuur

```
wescaleup-manager/
├── wescaleup-manager.php       ← hoofdbestand
├── includes/
│   └── class-updater.php       ← GitHub auto-update logica
└── modules/
    ├── beveiliging.php
    ├── admin-branding.php
    ├── svg-upload.php
    ├── reacties.php
    ├── password-page.php
    └── dashboard.php
```
