# Chat-api dokumentáció

Laravel alapú REST api

## Szükséges
- PHP **8.3** vagy újabb
- MySQL adatbázis

## Telepítés

1. A projekt gyökérkönyvtárban futtani kell a következő parancsot
```
composer setup
```
ez elvégez minden szükséges a műveletet a helyes működéshez.

2. Laravel fejlesztői szerver indítása
```
php artisan serve
```
ez után az api a itt lesz előrhető
```
http://127.0.0.1:8000/api
```
## Hitelesítés
Minden végpont, ami a felhasználó adatait módosítja vagy lekéri hitelesítést igényel.  
Minden kéréshez **Bearer token**-t kell csatolni a `Authorization` header-ben.

## API végpontok
Az alábbiak mind **Sanctum hitelesítést** igényelnek, kivéve a **login**, **register** és az **email verifikáció** végpontjait.

### Auth

**POST /register**  
Regisztráció új felhasználó létrehozásához.

Body:
```
{
    "name": "Example John",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

**POST /login**  
Bejelentkezés és token generálás.

Body:
```
{
    "email": "john@example.com",
    "password": "password"
}
```
****
### Bejelentkezett felhasználó

**GET /user**  
A bejeletkezett felhasználó adatai.
****
### Felhasználók

**GET /users**  
Aktív (email átal megerősített) felhasználók listája, **lapozható és szűrhető**.  
Alapértelmezetten 5 felhasználó jelenik meg egy oldalon, ez a `per_page` paraméter megadásával változtatható.

| Paraméter | Típus | Leírás | Példa |
| --------- | ----- | ------ | ----- |
| page | int | Aktuális lap | 1 |
| per_page | int | Elemszámok laponként | 10 |
| search | string | Név vagy email szűrés | John |

**GET /users/{id}**  
Egy felhasználó lekérdezése ID alapján.
****
### Barátkezelés

**GET /friends**  
A bejelentkezett felhasználó barátjainak listája.
*****
### Ismerős jelölések

**GET /friend-requests**  
A bejelentkezett felhaszáló összes barátkérése.

**GET /friend-requests/incoming**  
A bejelentkezett felhaszálónak érkező barátkérések.

**GET /friend-requests/outgoing**  
A bejelentkezett felhaszáló átal küldött barátkérések.

**POST /friend-requests**  
Barátkérelem küldése.

Body:
```
{
    "friend_id": 3
}
```

**PATCH /friend-requests/{id}**  
Barátkérés elfogadása vagy elutasítása.

Body
```
{
    "status": "accept"
}
```
vagy
```
{
    "status": "decline"
}
```
****
### Üzenetkezelés

**POST /message**  
Új üzenet küldése.

Body:
```
{
    "receiver_id": 3,
    "message": "Hi!"
}
```

**GET /messages**  
A bejelentkezett felhasználó beszélgetéseinek a listázása.

**GET /messages/{userId}**  
Összes üzenet egy adott felhasználóval.

****
### Email verifikáció

**GET /email/verify/{id}/{hash}**  
Email cím hitelesítése.

## Tesztelés
A tesztek a `test/Feature` mappában találhatóak. Minden teszt független egymástól a `RefreshDatabase` trait miatt.  
A tesztek futtathatóak a következő parancsal.
```
php artisan test
```