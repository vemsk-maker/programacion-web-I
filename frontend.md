# PSF — Guía de trabajo Frontend

> **Para la desarrolladora frontend.**
> Lee este archivo de principio a fin antes de escribir la primera línea de código.
> Toda la información que necesitas para construir las vistas Blade está aquí.

---

## Tabla de contenidos

1. [Contexto del proyecto](#1-contexto-del-proyecto)
2. [Reglas generales del proyecto](#2-reglas-generales-del-proyecto)
3. [Estructura de archivos de vistas](#3-estructura-de-archivos-de-vistas)
4. [Guía de Alpine.js en este proyecto](#4-guía-de-alpinejs-en-este-proyecto)
5. [Especificación de vistas](#5-especificación-de-vistas)
   - 5.1 [Dashboard](#51-dashboard)
   - 5.2 [Categorías](#52-categorías)
   - 5.3 [Proveedores](#53-proveedores)
   - 5.4 [Productos](#54-productos)
   - 5.5 [Compras](#55-compras)
   - 5.6 [Transferencias](#56-transferencias)
   - 5.7 [Ventas / POS](#57-ventas--pos)
   - 5.8 [Reportes](#58-reportes)
6. [Orden de trabajo recomendado](#6-orden-de-trabajo-recomendado)
7. [Comandos útiles](#7-comandos-útiles)

---

## 1. Contexto del proyecto

**PSF** es un sistema de gestión de inventario multi-sucursal diseñado para un negocio con tres ubicaciones:

- **Almacén Central** — bodega principal de recepción y almacenamiento
- **Supermercado** — punto de venta 1
- **Licorería** — punto de venta 2

### Stack tecnológico

| Capa | Tecnología |
|---|---|
| Framework backend | Laravel 12 (PHP 8.2+) |
| Base de datos | PostgreSQL |
| UI base | TailAdmin (Tailwind CSS v4) |
| Interactividad frontend | Alpine.js 3.x |
| Build tool | Vite 7 |

### Tu rol

El backend está **completamente terminado**: migraciones, modelos, enums, servicios, triggers de PostgreSQL, seeders, middleware y todos los controladores. Tu trabajo es construir las vistas Blade sobre esa base.

**No tocarás PHP. No tocarás rutas. No tocarás servicios.**
Solo crearás y editarás archivos en `resources/views/`.

---

## 2. Reglas generales del proyecto

### ✅ MUST — siempre hacer esto

- **Todas las vistas extienden el layout principal de TailAdmin.**
  ```blade
  @extends('layouts.app')
  @section('content')
      {{-- tu contenido --}}
  @endsection
  ```

- **Usar componentes Blade de TailAdmin** para inputs, selects, tablas, cards y badges. No reinventes componentes que ya existen.

- **Mensajes flash de éxito/error** usando el componente de alerta de TailAdmin. Las variables disponibles son `session('success')` y `session('error')`.
  ```blade
  @if(session('success'))
      <div class="rounded-lg bg-success-50 border border-success-200 px-4 py-3 text-sm text-success-700
                  dark:bg-success-500/10 dark:border-success-500/20 dark:text-success-400">
          {{ session('success') }}
      </div>
  @endif
  ```

- **Paginación** con el helper de Laravel dentro del layout TailAdmin:
  ```blade
  {{ $items->links() }}
  ```

- **Formularios** siempre con `@csrf`. Para PUT/PATCH/DELETE usar `@method('PUT')` etc.
  ```blade
  <form method="POST" action="{{ route('categories.update', $category) }}">
      @csrf
      @method('PUT')
      ...
  </form>
  ```

- **Estado vacío** en todas las tablas: cuando no haya registros mostrar un mensaje explicativo en lugar de una tabla vacía.

- **Consistencia visual** entre todas las pantallas: mismos colores de badge, misma estructura de cabecera de página, mismo espaciado.

### 🚫 NEVER — nunca hacer esto

- No escribir lógica de negocio en las vistas (sin queries Eloquent, sin cálculos complejos en PHP dentro del Blade).
- No usar estilos `style=""` inline ni clases Tailwind que no formen parte del sistema de diseño TailAdmin.
- No modificar controladores, modelos, rutas ni ningún archivo `.php`.
- No instalar nuevos paquetes npm sin coordinar con el líder del proyecto.

---

## 3. Estructura de archivos de vistas

El árbol completo que debe existir al terminar el trabajo:

```
resources/views/
├── layouts/              ← ya existe en TailAdmin, NO modificar
├── components/           ← ya existe en TailAdmin, puedes agregar componentes propios aquí
├── dashboard/
│   └── index.blade.php
├── categories/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── suppliers/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── products/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── purchases/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── show.blade.php
├── transfers/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── show.blade.php
├── sales/
│   ├── index.blade.php
│   ├── create.blade.php   ← pantalla POS
│   └── show.blade.php     ← recibo
└── reports/
    ├── dashboard.blade.php
    ├── expirations.blade.php
    ├── stock.blade.php
    └── movements.blade.php
```

---

## 4. Guía de Alpine.js en este proyecto

### No necesitas importar nada

Alpine.js ya está incluido por TailAdmin en el bundle de Vite. Solo úsalo directamente en el HTML.

### Patrón básico

```html
<div x-data="miComponente()">
    <input x-model="nombre" type="text" />
    <span x-show="nombre.length > 0">Hola, <span x-text="nombre"></span></span>
    <button x-on:click="limpiar()">Limpiar</button>
</div>

<script>
function miComponente() {
    return {
        nombre: '',
        limpiar() { this.nombre = '' }
    }
}
</script>
```

### Directivas más usadas en PSF

| Directiva | Uso |
|---|---|
| `x-data="{ ... }"` | Define el estado del componente |
| `x-model="propiedad"` | Enlace bidireccional con un input |
| `x-show="condicion"` | Muestra/oculta según condición |
| `x-if="condicion"` | Renderiza/elimina del DOM (dentro de `<template>`) |
| `x-for="item in lista"` | Itera sobre un array (dentro de `<template>`) |
| `x-text="expresion"` | Escribe texto dentro del elemento |
| `x-on:click="metodo()"` | Escucha eventos (abreviado: `@click`) |
| `x-on:keydown.enter` | Captura Enter (útil para búsquedas con scanner) |
| `:class="{ 'clase': cond }"` | Clases condicionales |
| `:disabled="condicion"` | Atributo deshabilitado reactivo |
| `x-ref="nombre"` | Referencia a elemento DOM (como `$refs.nombre`) |

### Llamadas al backend desde Alpine.js

Usa `fetch()` nativo. Los controladores ya tienen los endpoints habilitados.

```javascript
async buscarProducto(q, locationId) {
    if (q.length < 2) { this.resultados = []; return; }
    const res = await fetch(`/sales/search-product?q=${encodeURIComponent(q)}&location_id=${locationId}`);
    this.resultados = await res.json();
},
```

### Endpoints JSON disponibles

#### `GET /sales/search-product`

Busca productos por nombre o código de barras dentro de una ubicación.

```
Parámetros:
  q           — texto de búsqueda (nombre parcial) o código de barras exacto
  location_id — ID de la ubicación para filtrar stock disponible

Respuesta (array):
[
  {
    "id": 1,
    "name": "Arroz 1kg",
    "unit_of_measure": "kg",
    "use_batches": false,
    "stock": 45.00
  }
]
```

#### `GET /inventory/transfers/stock`

Consulta el stock disponible de un producto (y lote, si aplica) en una ubicación.

```
Parámetros:
  product_id  — ID del producto
  location_id — ID de la ubicación de origen
  batch_id    — ID del lote (opcional, solo si use_batches=true)

Respuesta:
{
  "quantity": 12.50
}
```

### Ejemplo completo: carrito de líneas

Este patrón se usa en `purchases/create` y `transfers/create`:

```javascript
function carritoCompra() {
    return {
        lineas: [],
        resultadosBusqueda: [],
        consultando: false,

        get total() {
            return this.lineas.reduce((sum, l) => sum + (l.cantidad * l.costo_unit), 0);
        },

        agregarLinea() {
            this.lineas.push({
                producto_id: null,
                producto_nombre: '',
                use_batches: false,
                batch_code: '',
                expiration_date: '',
                cantidad: 1,
                costo_unit: 0,
            });
        },

        quitarLinea(index) {
            this.lineas.splice(index, 1);
        },

        async buscarProducto(index, q, locationId) {
            if (q.length < 2) return;
            this.consultando = true;
            const res = await fetch(`/sales/search-product?q=${encodeURIComponent(q)}&location_id=${locationId}`);
            this.resultadosBusqueda = await res.json();
            this.consultando = false;
        },

        seleccionarProducto(index, producto) {
            this.lineas[index].producto_id     = producto.id;
            this.lineas[index].producto_nombre = producto.name;
            this.lineas[index].use_batches     = producto.use_batches;
            this.resultadosBusqueda            = [];
        },
    }
}
```

---

## 5. Especificación de vistas

---

### 5.1 Dashboard

---

#### `dashboard/index.blade.php`

**Ruta:** `resources/views/dashboard/index.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$expiringSoon` | `int` | Cantidad de lotes que vencen en los próximos 30 días |
| `$expiredWithStock` | `int` | Cantidad de lotes ya vencidos con stock > 0 |
| `$totalProducts` | `int` | Total de productos activos en el sistema |
| `$todayMovements` | `int` | Cantidad de movimientos registrados hoy |

**Qué debe mostrar:**

- Si `$expiredWithStock > 0`: banner de alerta rojo al tope de la página con texto urgente y link al reporte de vencimientos.
- 4 cards de estadísticas en fila:
  1. **Lotes próximos a vencer** (`$expiringSoon`) — ícono de reloj, color naranja/amarillo
  2. **Lotes vencidos** (`$expiredWithStock`) — ícono de advertencia, color rojo si > 0
  3. **Productos activos** (`$totalProducts`) — ícono de caja
  4. **Movimientos hoy** (`$todayMovements`) — ícono de actividad
- Sección de accesos rápidos con links a los módulos principales: Catálogo, Compras, Transferencias, Ventas, Reportes.

---

### 5.2 Categorías

---

#### `categories/index.blade.php`

**Ruta:** `resources/views/categories/index.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$categories` | `LengthAwarePaginator` | Categorías paginadas |
| `$search` | `string` | Texto de búsqueda activo (puede estar vacío) |

**Qué debe mostrar:**

- Formulario de búsqueda por nombre (GET, con `name="search"`, valor inicial `{{ $search }}`).
- Botón "Nueva categoría" que lleva a `route('categories.create')`.
- Tabla con columnas: **Nombre**, **Categoría padre** (mostrar `'Raíz'` si es null), **Acciones**.
- Acciones por fila: botón Editar (`route('categories.edit', $cat)`), botón Eliminar (form con `@method('DELETE')` y `confirm()` en JS).
- Estado vacío si no hay categorías.
- Paginación al pie.

---

#### `categories/create.blade.php`

**Ruta:** `resources/views/categories/create.blade.php`

**Variables disponibles:** ninguna adicional (solo las del layout).

**Qué debe mostrar:**

- Formulario POST a `route('categories.store')`.
- Campo `name` (texto, requerido).
- Select `parent_id` (opcional): lista plana de categorías existentes con opción vacía "Sin categoría padre". Las categorías se cargan en el controlador.
- Botones: Guardar, Cancelar (link a index).
- Errores de validación con `$errors->first('campo')`.

---

#### `categories/edit.blade.php`

**Ruta:** `resources/views/categories/edit.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$category` | `Category` | Categoría que se está editando |

**Qué debe mostrar:**

- Igual que `create`, pero form a `route('categories.update', $category)` con `@method('PUT')`.
- Valores prellenados: `old('name', $category->name)`, `old('parent_id', $category->parent_id)`.
- El select de padre no debe incluir la categoría actual como opción (para evitar auto-referencia).

---

### 5.3 Proveedores

---

#### `suppliers/index.blade.php`

**Ruta:** `resources/views/suppliers/index.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$suppliers` | `LengthAwarePaginator` | Proveedores paginados |
| `$search` | `string` | Texto de búsqueda activo |

**Qué debe mostrar:**

- Búsqueda por nombre (GET).
- Botón "Nuevo proveedor" → `route('suppliers.create')`.
- Tabla: **Nombre**, **NIT**, **Estado** (badge verde "Activo" / gris "Inactivo"), **Acciones**.
- Acciones: Editar, Toggle de estado, Eliminar.
  - Toggle de estado: form POST a `route('suppliers.toggle', $supplier)` con `@method('PATCH')`. El botón dice "Desactivar" si está activo o "Activar" si está inactivo.
- Estado vacío.
- Paginación.

---

#### `suppliers/create.blade.php`

**Ruta:** `resources/views/suppliers/create.blade.php`

**Variables disponibles:** ninguna adicional.

**Qué debe mostrar:**

- Formulario POST a `route('suppliers.store')`.
- Campos: `name` (requerido), `nit` (opcional), `contact_info` (textarea opcional), `active` (checkbox, marcado por defecto).
- Botones: Guardar, Cancelar.

---

#### `suppliers/edit.blade.php`

**Ruta:** `resources/views/suppliers/edit.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$supplier` | `Supplier` | Proveedor que se está editando |

**Qué debe mostrar:**

- Igual que `create`, form PUT a `route('suppliers.update', $supplier)`.
- Valores prellenados con `old('campo', $supplier->campo)`.

---

### 5.4 Productos

---

#### `products/index.blade.php`

**Ruta:** `resources/views/products/index.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$products` | `LengthAwarePaginator` | Productos paginados |
| `$categories` | `Collection` | Todas las categorías (para el filtro select) |
| `$search` | `string` | Texto de búsqueda activo |
| `$categoryFilter` | `int\|null` | ID de categoría seleccionada en el filtro |

**Qué debe mostrar:**

- Filtros: búsqueda por nombre + select de categoría.
- Botón "Nuevo producto" → `route('products.create')`.
- Tabla: **Nombre**, **Categoría**, **Unidad de medida**, **Lotes** (badge "Control de lotes" si `use_batches=true`), **Estado** (badge Activo/Inactivo), **Acciones** (Ver, Editar, Eliminar).
- Estado vacío.
- Paginación.

---

#### `products/create.blade.php`

**Ruta:** `resources/views/products/create.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$categories` | `Collection` | Categorías para el select |

**Qué debe mostrar:**

- Formulario POST a `route('products.store')`.
- Campos del producto: `name`, `description` (textarea), `category_id` (select), `unit_of_measure` (text), `use_batches` (checkbox con tooltip: "Activa el seguimiento de lotes y fechas de vencimiento"), `active` (checkbox, marcado por defecto).
- **Sección de códigos de barras con Alpine.js** (`x-data`):
  - Encabezado "Códigos de barras" + botón "Agregar código".
  - Lista de filas dinámicas. Cada fila tiene:
    - Input `barcodes[{i}][barcode]` para el código.
    - Input `barcodes[{i}][units_per_scan]` (número, default 1) para unidades por escaneo.
    - Botón quitar fila (⊗).
  - Cuando no hay filas: mensaje "Sin códigos de barras registrados".
- Botones: Guardar, Cancelar.

---

#### `products/edit.blade.php`

**Ruta:** `resources/views/products/edit.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$product` | `Product` | Producto con relación `barcodes` cargada |
| `$categories` | `Collection` | Categorías para el select |

**Qué debe mostrar:**

- Igual que `create`, form PUT a `route('products.update', $product)`.
- Valores prellenados para todos los campos del producto.
- Sección de códigos de barras inicializada con los códigos existentes:
  ```javascript
  x-data="{ barcodes: {{ $product->barcodes->map(fn($b) => ['barcode' => $b->barcode, 'units_per_scan' => $b->units_per_scan])->toJson() }} }"
  ```

---

#### `products/show.blade.php`

**Ruta:** `resources/views/products/show.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$product` | `Product` | Producto con relaciones `category` y `barcodes` cargadas |
| `$stockByLocation` | `Collection` | Stock por sucursal: `[{ location_name, quantity, unit_of_measure }]` |

**Qué debe mostrar:**

- Cabecera con nombre del producto, categoría, unidad de medida, estado (badge), y si tiene control de lotes.
- Card "Códigos de barras": tabla con columnas Código y Unidades/Escaneo. Si no hay: mensaje vacío.
- Card "Stock actual por sucursal": tabla con columnas Sucursal, Cantidad, Unidad. Si no hay stock: mensaje vacío.
- Botones: Editar → `route('products.edit', $product)`, Volver → `route('products.index')`.

---

### 5.5 Compras

---

#### `purchases/index.blade.php`

**Ruta:** `resources/views/purchases/index.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$purchases` | `LengthAwarePaginator` | Compras paginadas con relaciones cargadas |
| `$suppliers` | `Collection` | Proveedores para el filtro |
| `$dateFrom` | `string` | Fecha inicio del filtro (formato `Y-m-d`) |
| `$dateTo` | `string` | Fecha fin del filtro |

**Qué debe mostrar:**

- Filtros: rango de fechas (date_from, date_to) + select de proveedor.
- Botón "Registrar compra" → `route('purchases.create')`.
- Tabla: **Fecha**, **Proveedor**, **Destino** (ubicación), **Referencia**, **Total (Bs)**, **Acciones** (Ver).
- Estado vacío.
- Paginación.

---

#### `purchases/create.blade.php`

**Ruta:** `resources/views/purchases/create.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$locations` | `Collection` | Ubicaciones activas (destino de la compra) |
| `$suppliers` | `Collection` | Proveedores activos |

**Qué debe mostrar:**

Formulario POST a `route('purchases.store')` con Alpine.js (`x-data`).

Cabecera del formulario (campos sin Alpine):
- Select `location_id` (ubicación destino, requerido).
- Select `supplier_id` (proveedor, requerido).
- Input `reference_doc` (texto, opcional) para número de factura/remito.
- Textarea `notes` (opcional).

Sección de líneas con Alpine.js:
- Botón "Agregar producto".
- Tabla de líneas. Cada fila:
  - Input de búsqueda de producto (fetch a `/sales/search-product` pasando el `location_id` seleccionado). Al seleccionar un resultado, se llena el `product_id` oculto y el nombre visible.
  - Si `use_batches=true`: input `batch_code` + input `expiration_date` (date).
  - Input `quantity` (número, min 0.01).
  - Input `unit_cost` (número, min 0) para el precio de compra.
  - Campo calculado: **subtotal** = `quantity * unit_cost` (solo texto, no input).
  - Botón quitar fila.
- Total general (suma de subtotales) en tiempo real.

El formulario se envía de forma clásica (form submit, no fetch). Los inputs de las líneas se nombran como arrays: `lines[0][product_id]`, `lines[0][quantity]`, etc.

---

#### `purchases/show.blade.php`

**Ruta:** `resources/views/purchases/show.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$purchase` | `array\|object` | Compra con `movementGroup`, `movements`, `product`, `batch` cargados |

**Qué debe mostrar:**

- Cabecera: proveedor, fecha, ubicación destino, referencia del documento, notas.
- Tabla de líneas: **Producto**, **Lote**, **Vencimiento** (si aplica), **Cantidad**, **Costo Unit. (Bs)**, **Subtotal (Bs)**.
- Total al pie de la tabla.
- Botón Volver → `route('purchases.index')`.

---

### 5.6 Transferencias

---

#### `transfers/index.blade.php`

**Ruta:** `resources/views/transfers/index.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$transfers` | `LengthAwarePaginator` | Transferencias paginadas |
| `$locations` | `Collection` | Ubicaciones (para filtros origen/destino) |
| `$dateFrom` | `string` | Fecha inicio del filtro |
| `$dateTo` | `string` | Fecha fin del filtro |

**Qué debe mostrar:**

- Filtros: rango de fechas, select origen, select destino.
- Botón "Nueva transferencia" → `route('transfers.create')`.
- Tabla: **Fecha**, **Origen**, **Destino**, **Usuario**, **Notas**, **Acciones** (Ver).
- Estado vacío.
- Paginación.

---

#### `transfers/create.blade.php`

**Ruta:** `resources/views/transfers/create.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$locations` | `Collection` | Ubicaciones activas |

**Qué debe mostrar:**

Formulario POST a `route('transfers.store')` con Alpine.js.

Cabecera:
- Select `origin_location_id` (origen, requerido). Al cambiar: limpiar todas las líneas.
- Select `destination_location_id` (destino, requerido). Debe ser diferente al origen (validación client-side).
- Textarea `notes` (opcional).

Sección de líneas (Alpine.js):
- Botón "Agregar línea".
- Cada fila:
  - Buscador de producto (fetch a `/sales/search-product` con el origen seleccionado).
  - Badge de stock disponible: al seleccionar un producto, consultar `/inventory/transfers/stock` y mostrar la cantidad disponible en el origen.
  - Si `use_batches=true`: select de lote. Al cambiar el lote, volver a consultar el stock disponible para ese lote.
  - Input `quantity` (número). Validación client-side: no puede superar el stock disponible. Mostrar error en rojo si se excede.
  - Botón quitar fila.

Los inputs se nombran como arrays: `lines[0][product_id]`, `lines[0][batch_id]`, `lines[0][quantity]`.

---

#### `transfers/show.blade.php`

**Ruta:** `resources/views/transfers/show.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$transfer` | `object` | Transferencia con `movements`, `originLocation`, `destinationLocation`, `user` cargados |

**Qué debe mostrar:**

- Cabecera: **Origen → Destino**, fecha, usuario que registró, notas.
- Tabla: **Producto**, **Lote** (si aplica), **Cantidad**.
- Botón "Imprimir" (`onclick="window.print()"`), visible solo en pantalla.
- Botón Volver → `route('transfers.index')`.

**Estilos de impresión:**

```blade
<style>
    @media print {
        nav, aside, .no-print { display: none !important; }
        body { font-size: 12pt; }
    }
</style>
```

---

### 5.7 Ventas / POS

---

#### `sales/create.blade.php` — Pantalla POS

**Ruta:** `resources/views/sales/create.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$locations` | `Collection` | Ubicaciones asignadas al usuario actual |
| `$currentLocation` | `Location\|null` | Ubicación activa (primera asignada al usuario) |

**Qué debe mostrar:**

Layout de **dos columnas**:

**Columna izquierda (3/5 del ancho):**
- Select de ubicación (`location_id`). Al cambiar: limpiar el carrito y los resultados de búsqueda.
- Input de búsqueda con `autofocus` y `x-ref="searchInput"`. Atributos: `@input.debounce.300ms="buscar()"` + `@keydown.enter="agregarPrimeroResultado()"`. Este input también funciona con lectores de código de barras USB HID (emiten eventos de teclado idénticos).
- Lista de resultados de búsqueda (dropdown). Cada resultado muestra nombre + stock. Al hacer click: `agregarAlCarrito(producto)`.
- Tabla del carrito con columnas: **Producto**, **Cantidad** (input editable), **Precio Unit. (Bs)** (input editable), **Subtotal**, **Quitar** (botón ✕).
- Estado vacío del carrito con ícono.

**Columna derecha (2/5 del ancho):**
- Card con inputs opcionales: `client_name` (texto) y `client_nit` (texto).
- Card de resumen: lista de subtotales por línea + **Total (Bs)** en grande.
- Botón "Confirmar venta" (deshabilitado si el carrito está vacío o si `submitting=true`).

**Comportamiento especial:**

El botón de confirmar envía la venta via `fetch()` POST a `/sales` con `Content-Type: application/json`. Payload:
```json
{
  "location_id": 1,
  "client_name": "...",
  "client_nit": "...",
  "lines": [
    { "product_id": 1, "quantity": 2, "unit_price": 15.50 }
  ]
}
```

Respuesta JSON exitosa (HTTP 201):
```json
{ "doc_number": "SUP-2026-00001", "id": 42 }
```

- **Éxito:** mostrar banner verde con el número de recibo generado + link a `route('sales.show', {id})` + botón "Nueva venta" que limpia el carrito y restaura el focus en el buscador.
- **Error:** mostrar banner rojo con el mensaje de error. **No perder el carrito.**

El input de búsqueda debe recuperar el foco automáticamente después de agregar un producto al carrito (`this.$refs.searchInput.focus()`).

---

#### `sales/index.blade.php`

**Ruta:** `resources/views/sales/index.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$sales` | `LengthAwarePaginator` | Ventas paginadas |
| `$dateFrom` | `string` | Fecha inicio del filtro (default: hoy) |
| `$dateTo` | `string` | Fecha fin del filtro |

**Qué debe mostrar:**

- Filtros: `date_from`, `date_to`. Si el usuario es admin: también select `location_id`.
- Botón "Nueva venta" → `route('sales.create')`.
- Tabla: **N° Recibo**, **Hora/Fecha**, **Cliente**, **Sucursal**, **Cajero**, **Total (Bs)**, **Estado** (badge), **Acciones**.
- Acciones:
  - Botón "Ver" → `route('sales.show', $sale)`.
  - Botón "Cancelar": solo visible si `status='open'` Y el usuario autenticado es `master` o `admin`. Usa form POST a `route('sales.cancel', $sale)` con `@method('POST')` y `onsubmit="return confirm('¿Cancelar esta venta?')"`.
- Badges de estado: "Abierto" (verde), "Cancelado" (rojo), "Cerrado" (gris).
- Estado vacío.
- Paginación.

**Condicional de rol:**

```blade
@if(auth()->user()->isAdmin())
    {{-- mostrar select de ubicación en filtros --}}
    {{-- mostrar botón cancelar --}}
@endif
```

Los helpers disponibles en el modelo `User` son: `isAdmin()`, `isCashier()`, `hasLocationAccess($locationId)`.

---

#### `sales/show.blade.php` — Recibo

**Ruta:** `resources/views/sales/show.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$sale` | `Document` | Documento con `movementGroup.movements.product`, `client_name`, `client_nit` cargados |

**Qué debe mostrar:**

- Botón "Imprimir" (`onclick="window.print()"`) — visible solo en pantalla.
- **Cabecera del recibo:** nombre del negocio/sistema, número de recibo, fecha y hora, sucursal, cajero.
- Card datos del cliente (solo si `$sale->client_name` o `$sale->client_nit` no son null).
- Tabla de productos: **Nombre**, **Cantidad**, **Precio Unit. (Bs)**, **Subtotal (Bs)**.
- Total al pie.
- Botones Volver → `route('sales.index')`, Cancelar (solo si `isAdmin()` y `status='open'`).

**Estilos de impresión:**

```blade
<style>
    @media print {
        nav, aside, .no-print, form { display: none !important; }
        .print-only { display: block !important; }
        body { font-size: 11pt; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #000; padding: 4px 8px; }
    }
</style>
```

---

### 5.8 Reportes

---

#### `reports/dashboard.blade.php`

**Ruta:** `resources/views/reports/dashboard.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$expiringSoon` | `int` | Lotes que vencen en los próximos 30 días con stock > 0 |
| `$expiredWithStock` | `int` | Lotes ya vencidos con stock > 0 |
| `$totalProducts` | `int` | Total de productos activos |
| `$todayMovements` | `int` | Movimientos registrados hoy |

**Qué debe mostrar:**

- Si `$expiredWithStock > 0`: banner de alerta rojo al tope con texto urgente y link a `route('reports.expirations', ['days' => 0])`.
- 4 cards de estadísticas (igual que el dashboard principal).
- 3 cards de links rápidos: Vencimientos → `route('reports.expirations')`, Stock → `route('reports.stock')`, Movimientos → `route('reports.movements')`.

---

#### `reports/expirations.blade.php`

**Ruta:** `resources/views/reports/expirations.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$batches` | `Collection` | Lotes próximos a vencer o ya vencidos |
| `$days` | `int` | Valor activo del filtro (0 = solo vencidos) |
| `$locationFilter` | `int\|null` | ID de ubicación filtrada |
| `$locations` | `Collection` | Todas las ubicaciones (para el select de filtro) |

**Qué debe mostrar:**

- Filtros: select de días (opciones: 0 "Solo vencidos", 15, 30, 60, 90) + select de ubicación.
- Botón "Exportar CSV" → link a la misma URL con `?export=1` añadido.
- Leyenda de colores de badges.
- Tabla: **Urgencia** (badge), **Lote**, **Producto**, **Ubicación**, **Vencimiento**, **Stock**.

**Badges de urgencia:**

| Condición | Texto | Color |
|---|---|---|
| Ya vencido | "Vencido" | Rojo |
| ≤ 15 días | "Vence en X días" | Naranja |
| ≤ 30 días | "Vence en X días" | Amarillo |
| ≤ 60 días | "Vence en X días" | Verde |

Para calcular los días restantes en la vista:
```blade
@php $diff = \Carbon\Carbon::now()->diffInDays($batch->expiration_date, false) @endphp
```
`$diff` será negativo si ya venció.

---

#### `reports/stock.blade.php`

**Ruta:** `resources/views/reports/stock.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$rows` | `LengthAwarePaginator` | Filas de stock paginadas |
| `$locations` | `Collection` | Ubicaciones para el filtro |
| `$categories` | `Collection` | Categorías para el filtro |
| `$locationFilter` | `int\|null` | Ubicación seleccionada en el filtro |
| `$categoryFilter` | `int\|null` | Categoría seleccionada en el filtro |
| `$canCost` | `bool` | `true` si el rol puede ver costos (master/admin/warehouse_manager) |
| `$totalValue` | `float\|null` | Valor total del inventario en Bs (null si `$canCost=false`) |

**Qué debe mostrar:**

- Filtros: select de categoría + select de ubicación.
- Si `$canCost`: card de valorización con "Valor total del inventario: **Bs {totalValue}**".
- Botón "Exportar CSV".
- Tabla con columnas: **Producto**, **Categoría**, **Ubicación**, **Cantidad**, **Unidad**, y **condicionalmente** (si `$canCost`): **Costo Unit. (Bs)**, **Valor Total (Bs)**.
- Paginación.

---

#### `reports/movements.blade.php`

**Ruta:** `resources/views/reports/movements.blade.php`

**Variables disponibles:**

| Variable | Tipo | Descripción |
|---|---|---|
| `$movements` | `LengthAwarePaginator` | Movimientos paginados con relaciones cargadas |
| `$products` | `Collection` | Productos para el filtro select |
| `$locations` | `Collection` | Ubicaciones para el filtro |
| `$types` | `array` | Casos del enum `MovementType` |
| `$productId` | `int\|null` | Producto seleccionado en el filtro |
| `$locationId` | `int\|null` | Ubicación seleccionada en el filtro |
| `$type` | `string\|null` | Tipo de movimiento seleccionado |
| `$dateFrom` | `string` | Fecha inicio del filtro |
| `$dateTo` | `string` | Fecha fin del filtro |
| `$canCost` | `bool` | `true` si el rol puede ver costo unitario |

**Qué debe mostrar:**

- Filtros: select de producto, select de ubicación, select de tipo, input date_from, input date_to.
- Botón limpiar filtros (link a `route('reports.movements')` sin parámetros).
- Botón "Exportar CSV".
- Leyenda de badges por tipo.
- Tabla: **Fecha**, **Tipo** (badge), **Producto**, **Lote**, **Origen**, **Destino**, **Cantidad**, (si `$canCost`) **Costo Unit. (Bs)**, **Usuario**, **Referencia**.
- Estado vacío.
- Paginación.

**Badges por tipo de movimiento:**

| Tipo | Texto | Color |
|---|---|---|
| `purchase` | Compra | Verde |
| `sale` | Venta | Azul |
| `transfer` | Traslado | Amarillo |
| `adjustment` | Ajuste | Gris |
| `waste` | Baja | Rojo |

Etiquetas en español para el filtro select de tipos:
```blade
@foreach($types as $t)
    <option value="{{ $t->value }}" {{ $type === $t->value ? 'selected' : '' }}>
        {{ match($t->value) {
            'purchase'   => 'Compra',
            'sale'       => 'Venta',
            'transfer'   => 'Traslado',
            'adjustment' => 'Ajuste',
            'waste'      => 'Baja',
            default      => $t->value,
        } }}
    </option>
@endforeach
```

---

## 6. Orden de trabajo recomendado

Sigue este orden: de menor a mayor complejidad. Esto te permite tener vistas funcionales rápido y afrontar lo más difícil cuando ya tienes el contexto del proyecto.

### Prioridad 1 — Vistas estáticas simples (sin Alpine.js)

Estas vistas solo necesitan Blade puro: loops, condicionales y formularios simples.

1. `categories/index.blade.php`
2. `categories/create.blade.php`
3. `categories/edit.blade.php`
4. `suppliers/index.blade.php`
5. `suppliers/create.blade.php`
6. `suppliers/edit.blade.php`
7. `products/index.blade.php`
8. `products/show.blade.php`
9. `purchases/index.blade.php`
10. `purchases/show.blade.php`
11. `transfers/index.blade.php`
12. `transfers/show.blade.php`
13. `sales/index.blade.php`
14. `reports/dashboard.blade.php`
15. `reports/expirations.blade.php`
16. `reports/stock.blade.php`
17. `reports/movements.blade.php`

### Prioridad 2 — Formularios con Alpine.js básico

Estos formularios tienen una sección de líneas dinámicas pero no dependen de fetch para su funcionalidad principal.

18. `products/create.blade.php` (sección de barcodes dinámica)
19. `products/edit.blade.php` (idem, inicializada con datos del servidor)
20. `purchases/create.blade.php` (líneas de compra dinámicas + buscador fetch)
21. `transfers/create.blade.php` (líneas con consulta de stock disponible)

### Prioridad 3 — Pantallas más complejas

22. `sales/create.blade.php` — POS completo: carrito, búsqueda, submit fetch, manejo de respuesta JSON
23. `sales/show.blade.php` — Recibo con estilos de impresión

---

## 7. Comandos útiles

```bash
# Compilar assets en modo desarrollo (con hot reload)
npm run dev

# Compilar assets para producción
npm run build

# Servidor local de Laravel
php artisan serve

# Cargar datos iniciales (roles, ubicaciones, usuario master)
php artisan db:seed

# Ejecutar migraciones
php artisan migrate

# Ver todas las rutas con sus nombres
php artisan route:list

# Filtrar rutas por módulo
php artisan route:list | grep "sales\."
php artisan route:list | grep "reports\."
```

### Credenciales del usuario de prueba

```
URL:      http://localhost:8000
Email:    master@psf.local
Password: secret_change_me
Rol:      master (acceso completo)
```

El usuario master tiene acceso a todas las pantallas. Para probar restricciones de rol, desde el panel de Administración puedes crear usuarios con los roles `cashier` o `viewer`.

---

*Este documento fue generado el 28/04/2026 y corresponde al estado de la Fase 2 del proyecto PSF.*
