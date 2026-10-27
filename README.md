# InkTrack API

Knygų vertinimo platformos „InkTrack“ REST API. Naudotojai gali naršyti žanrų ir knygų katalogą, skaityti atsiliepimus, o prisijungę – rašyti atsiliepimus ir vertinti knygas. Moderatoriai šalina netinkamus atsiliepimus, administratoriai valdo knygas, žanrus ir naudotojus.

## Technologijos

- PHP 8.4+, Laravel 13
- MySQL (Eloquent ORM)
- Autentifikacija ir autorizacija: JWT (`tymon/jwt-auth`), naudotojo rolė saugoma žetono viduje (claim `role`)
- API versija: `v1` (visi maršrutai pradedami `/api/v1`)

## Paleidimas

Reikalinga: PHP 8.4+, Composer, MySQL.

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

`.env` faile nurodykite MySQL prisijungimą (pavyzdžiui):

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saitynas_projektas
DB_USERNAME=root
DB_PASSWORD=
```

Sukurkite tuščią duomenų bazę `saitynas_projektas`, tada:

```bash
php artisan migrate:fresh --seed
php artisan serve
```

API pasiekiamas adresu `http://127.0.0.1:8000/api/v1`.

Prieš kiekvieną demonstraciją duomenis galima atstatyti: `php artisan migrate:fresh --seed`.

### Demonstraciniai naudotojai

Visų slaptažodis: `password123`

| El. paštas | Rolė |
|---|---|
| `test@example.com` | user |
| `moderator@example.com` | moderator |
| `admin@example.com` | admin |
| `reader1@example.com`, `reader2@example.com` | user |

Seeder'is sukuria 4 žanrus, 6 knygas ir 6 atsiliepimus.

## Autentifikacija

1. `POST /api/v1/auth/login` su el. paštu ir slaptažodžiu grąžina `access_token` (JWT, galioja 60 min.).
2. Apsaugotiems metodams siunčiama antraštė `Authorization: Bearer <access_token>`.
3. Žetono viduje (payload) yra `sub` (naudotojo ID), **`role`**, `iat`, `exp` ir `jti`. Autorizacija pagal rolę vykdoma skaitant rolę iš žetono.
4. `POST /api/v1/auth/logout` įrašo žetoną į juodąjį sąrašą (blacklist, `cache` lentelė), po to jis grąžina 401.
5. Registruojantis rolė visada būna `user`; ją pakeisti gali tik administratorius.

Pastaba: administratoriui pakeitus naudotojo rolę, jau išduotas žetonas turi senąją rolę, kol baigs galioti arba kol naudotojas prisijungs iš naujo.

### Rolės

Hierarchija: `user` < `moderator` < `admin` (aukštesnė rolė gali viską, ką žemesnė).

| Rolė | Galimybės |
|---|---|
| Neregistruotas | Registruotis, prisijungti, peržiūrėti žanrus, knygas (paieška, filtras pagal žanrą), atsiliepimus, naudotojo profilį |
| user | Atsijungti, peržiūrėti ir redaguoti savo profilį, kurti savo atsiliepimus, redaguoti ir šalinti savo atsiliepimus |
| moderator | Viskas, ką gali user, ir šalinti bet kurį atsiliepimą |
| admin | Viskas, ką gali moderator, ir kurti / redaguoti / šalinti žanrus ir knygas, valdyti naudotojus (keisti roles, šalinti paskyras) |

## API metodai

Bazinis adresas: `/api/v1`

| Metodas | Kelias | Prieiga | Aprašymas |
|---|---|---|---|
| POST | `/auth/register` | viešas | Registracija (201) |
| POST | `/auth/login` | viešas | Prisijungimas, grąžina JWT |
| GET | `/auth/me` | prisijungęs | Dabartinis naudotojas |
| POST | `/auth/logout` | prisijungęs | Atsijungimas (žetonas anuliuojamas) |
| GET | `/genres` | viešas | Žanrų sąrašas |
| GET | `/genres/{id}` | viešas | Žanras su knygomis |
| POST | `/genres` | admin | Sukurti žanrą |
| PUT | `/genres/{id}` | admin | Redaguoti žanrą |
| DELETE | `/genres/{id}` | admin | Šalinti žanrą |
| GET | `/books` | viešas | Knygų sąrašas. Parametrai: `search`, `title`, `author`, `genre_id` |
| GET | `/books/{id}` | viešas | Knyga su žanru ir atsiliepimais |
| POST | `/books` | admin | Sukurti knygą |
| PUT | `/books/{id}` | admin | Redaguoti knygą |
| DELETE | `/books/{id}` | admin | Šalinti knygą |
| GET | `/reviews` | viešas | Atsiliepimai. Parametrai: `book_id`, `user_id` |
| GET | `/reviews/{id}` | viešas | Vienas atsiliepimas |
| POST | `/reviews` | prisijungęs | Sukurti atsiliepimą (autorius imamas iš žetono) |
| PUT | `/reviews/{id}` | autorius | Redaguoti savo atsiliepimą |
| DELETE | `/reviews/{id}` | autorius, moderator, admin | Šalinti atsiliepimą |
| GET | `/users` | admin | Visi naudotojai |
| GET | `/users/{id}` | viešas | Viešas profilis (be el. pašto) |
| PUT | `/users/{id}` | savininkas | Redaguoti savo profilį |
| PUT | `/users/{id}/role` | admin | Keisti naudotojo rolę |
| DELETE | `/users/{id}` | admin | Šalinti naudotoją |

### Atsakymo kodai

| Kodas | Reikšmė |
|---|---|
| 200 | Sėkmingas atsakymas (taip pat sėkmingas šalinimas su `message`) |
| 201 | Resursas sukurtas |
| 401 | Nėra, netinkamas, pasibaigęs ar anuliuotas žetonas / neteisingi prisijungimo duomenys |
| 403 | Rolės ar nuosavybės teisių nepakanka |
| 404 | Resursas nerastas |
| 422 | Blogas payload (validacijos klaidos pateikiamos lauke `errors`) |

Atsiliepimo įvertinimas: nuo 1 iki 5, žingsniu 0.5. Vienas naudotojas gali įvertinti knygą tik kartą.

## Dokumentacija ir testavimas

- OpenAPI specifikacija: [`openapi.yaml`](docs/openapi.yaml). Galima atidaryti per [Swagger Editor](https://editor.swagger.io) (File → Import file).
- Postman kolekcija: [`InkTrack_API.postman_collection.json`](docs/InkTrack_API.postman_collection.json).

### Postman kolekcijos paleidimas

1. Paleiskite serverį (`php artisan serve`) ir atstatykite duomenis (`php artisan migrate:fresh --seed`).
2. Importuokite kolekciją į Postman. Kolekcijos kintamasis `base_url` pagal numatymą yra `http://localhost:8000/api/v1`.
3. Paleiskite visą kolekciją per **Runner**. Pirmiausia vykdomas aplankas `Auth`, kuris išsaugo žetonus (`user_token`, `moderator_token`, `admin_token`), todėl aplankų tvarkos keisti nereikia.

Kolekcija tikrina visus metodus, taip pat 401, 403, 404 ir 422 atvejus.

## Projekto struktūra (pagrindiniai failai)

```
InkTrack/
├── backend/
|   ├── app/Http/Controllers/Api/   # Auth, User, Book, Genre, Review valdikliai
|   ├── app/Http/Middleware/        # JwtAuthenticate, RoleMiddleware
|   ├── app/Models/                 # User, Book, Genre, Review
|   ├── database/migrations/        # lentelių schema
|   ├── database/seeders/           # demonstraciniai duomenys
|   ├── routes/api.php              # maršrutai (/api/v1)
|   └── config/jwt.php              # JWT nustatymai
├── openapi.yaml
└── InkTrack_API.postman_collection.json
```