# Sistema Web de Cotizacion de Servicios

Aplicacion web desarrollada con `PHP`, `MySQL`, `JavaScript` puro, `AJAX` y una organizacion tipo `MVC` para gestionar un catalogo de servicios, carrito dinamico, autenticacion por roles y generacion de cotizaciones.

## Funcionalidades incluidas

- Catalogo con 12+ servicios organizados en 3 categorias.
- Registro e inicio de sesion con sesiones PHP.
- Roles `admin` y `cliente`.
- Carrito dinamico con `AJAX`, cantidades minimas y maximas, contador de items y vaciado completo.
- Calculos automaticos de subtotal, descuento, IVA y total.
- Generacion de cotizaciones con codigo `COT-YYYY-####`.
- Historial de cotizaciones con vista responsive.
- Gestion de servicios para administrador.
- Peticiones AJAX centralizadas en `public/index.php` y resueltas por controladores.

## Requisitos

- XAMPP con Apache y MySQL activos.
- PHP 8.3+
- MySQL / MariaDB

## Instalacion

1. Copia el proyecto en:
   `C:\xampp\htdocs\DESLIS02`
2. Importa el archivo [bd.sql](/C:/xampp/htdocs/DESLIS02/bd.sql:1) en MySQL.
3. Verifica que la base de datos creada sea `cotizador_mvc`.
4. Inicia Apache y MySQL desde XAMPP.
5. Abre el sistema en:
   `http://localhost/DESLIS02/`

## Credenciales de prueba

- Administrador
  `admin@admin.com`
  `password123`
- Cliente
  `cliente@cliente.com`
  `password123`

Tambien puedes crear nuevas cuentas desde:
`http://localhost/DESLIS02/public/index.php?page=register`

## Estructura principal

```text
DESLIS02/
|-- app/
|   |-- config/
|   |   `-- database.php
|   |-- controllers/
|   |   |-- AuthController.php
|   |   |-- CartController.php
|   |   |-- QuoteController.php
|   |   `-- ServiceController.php
|   |-- models/
|   |   |-- Quote.php
|   |   |-- QuoteDetail.php
|   |   |-- Service.php
|   |   `-- User.php
|   `-- views/
|       |-- auth/
|       |   |-- login.php
|       |   `-- register.php
|       |-- quotes/
|       |   `-- history.php
|       |-- services/
|       |   `-- catalog.php
|       `-- home.php
|-- public/
|   |-- assets/
|   |   |-- css/
|   |   |   `-- services-catalog.css
|   |   `-- js/
|   |       |-- login.js
|   |       |-- register.js
|   |       `-- services-catalog.js
|   |-- fix_passwords.php
|   |-- index.php
|   `-- test_db.php
|-- bd.sql
`-- README.md
```

## Rutas principales

- Inicio: `http://localhost/DESLIS02/`
- Catalogo: `http://localhost/DESLIS02/public/index.php?page=services`
- Login: `http://localhost/DESLIS02/public/index.php?page=login`
- Registro: `http://localhost/DESLIS02/public/index.php?page=register`
- Cotizaciones: `http://localhost/DESLIS02/public/index.php?page=quotes`

## Endpoints AJAX

- `public/index.php?action=login`
- `public/index.php?action=register`
- `public/index.php?action=add_to_cart`
- `public/index.php?action=get_cart`
- `public/index.php?action=update_cart`
- `public/index.php?action=remove_from_cart`
- `public/index.php?action=generate_quote`
- `public/index.php?action=create_service`
- `public/index.php?action=delete_service`

## Documento de servicios

La lista base de servicios con nombre, descripcion, precio y categoria se encuentra en:

- [docs/servicios.csv](/C:/xampp/htdocs/DESLIS02/docs/servicios.csv:1)

## Notas

- Si las credenciales de prueba no funcionan despues de importar la base, ejecuta:
  `http://localhost/DESLIS02/public/fix_passwords.php`
- La aplicacion guarda usuarios, servicios y cotizaciones en MySQL.
- El carrito se mantiene en sesion PHP.
