# Sistema Web de Cotización de Servicios

Sistema web desarrollado en **PHP + JavaScript + AJAX** que permite generar cotizaciones de servicios de forma dinámica, aplicando descuentos, impuestos y persistencia de datos sin base de datos (uso de sesiones y JSON).

---

## Características Principales

- Catálogo de servicios organizado por categorías
- Carrito de compras dinámico
- Modificación de cantidades (mín. 1 – máx. 10)
- Cálculo automático de:
  - Subtotal
  - Descuento por cantidad
  - IVA (13%)
  - Total final
- Generación de cotizaciones con código único
- Historial de cotizaciones persistente (JSON)
- Validación dual (Frontend y Backend)
- Uso de Bootstrap para diseño responsivo

---

## Tecnologías Utilizadas

- PHP 8+
- JavaScript (JS puro)
- AJAX (fetch)
- Bootstrap 5
- JSON (persistencia)
- Sesiones PHP

---

## Estructura del Proyecto

DESLIS01
├── api/
│ ├── add-to-cart.php
│ ├── update-cart.php
│ ├── remove-item.php
│ ├── remove-from-cart.php
│ ├── get-cart.php
│ └── process-quote.php
│
├── classes/
│ ├── Service.class.php
│ └── Quote.class.php
│
├── pages/
│ ├── services-catalog.php
│ └── view-quotes.php
│
├── assets/
│ ├── js/
│ │ └── services-catalog.js
│ └── css/
│
├── data/
│ └── quotes.json
│
├── index.php
└── README.md

---

## Instalación

1. Copiar el proyecto en el directorio de Apache (XAMPP):
   C:\xampp\htdocs\DESLIS01

> Importante: La carpeta `data` es obligatoria para guardar las cotizaciones.

2. Iniciar Apache desde XAMPP.

3. Acceder desde el navegador:
   http://localhost/DESLIS01

---

## Uso del Sistema

1. Ingresar al sistema desde `index.php`
2. Entrar al **Catálogo de Servicios**
3. Agregar servicios al carrito
4. Modificar cantidades con botones **+ / -**
5. Presionar **Generar Cotización**
6. Completar datos del cliente en el modal
7. Confirmar cotización
8. Consultar el historial en **Ver Cotizaciones**

---

## Reglas de Negocio

### Descuento por Cantidad (Opción B)

- 3 a 5 unidades → 8%
- 6 a 9 unidades → 12%
- 10 o más unidades → 18%

### Validaciones

- Carrito no puede estar vacío
- Subtotal mínimo: $100
- Cantidad por servicio: mínimo 1, máximo 10
- Todos los datos del cliente son obligatorios
- Email con formato válido

---

## Persistencia de Datos

- El carrito se maneja con **sesiones PHP**
- Las cotizaciones se almacenan en:
  data/quotes.json

No se utiliza base de datos.

---

## Notas Importantes

- El sistema valida tanto en frontend como en backend
- El backend siempre controla los límites de cantidad
- El historial de cotizaciones se conserva aunque se cierre el navegador

---

## Desarrolladores:

Desafío realizado por:
Oscar Alejandro Villalobos Eguizábal VE220589
William Antonio Ramos Rodríguez RR210930

---
