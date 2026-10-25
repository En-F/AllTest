# Cómo ejecutar FP IA & Big Data · Exam Center

## Opción 1 · Windows con PHP instalado

1. Descomprime el ZIP.
2. Entra en la carpeta `FP-IA-BD-EXAMENES`.
3. Haz doble clic en `iniciar.bat`.
4. Se abrirá el servidor en:

`http://localhost:8080`

Si el navegador no se abre automáticamente, copia esa dirección manualmente.

## Comprobar PHP

Abre PowerShell:

```powershell
php -v
```

Si aparece `PHP 8.x`, está listo.

## Si PHP no está instalado

Puedes usar PHP oficial o XAMPP.

### Con XAMPP

Copia la carpeta `FP-IA-BD-EXAMENES` dentro de:

`C:\xampp\htdocs\`

Arranca Apache desde XAMPP y abre:

`http://localhost/FP-IA-BD-EXAMENES/`

## SQLite

La aplicación intenta utilizar `PDO SQLite`.

Para comprobarlo:

```powershell
php -m | findstr /I sqlite
```

Si ves `pdo_sqlite` y `sqlite3`, usará:

`data/fp_examenes.sqlite`

Si no están disponibles, la aplicación funciona en modo compatibilidad y guarda en:

`data/fp_examenes.json`

En una instalación estándar de PHP para Windows, si SQLite está desactivado, revisa `php.ini` y habilita las extensiones SQLite correspondientes.

## Linux / Kali

Comprueba PHP:

```bash
php -v
```

Arranca la aplicación:

```bash
php -S localhost:8080
```

Abre:

`http://localhost:8080`

Si necesitas PHP/SQLite en Debian/Kali, instala los paquetes PHP de tu distribución y el módulo SQLite correspondiente.

## Copias de seguridad

Cierra el servidor y copia la carpeta `data/`.

- Si se usa SQLite: conserva `fp_examenes.sqlite`.
- Si se usa compatibilidad JSON: conserva `fp_examenes.json`.

Para mover todo a otro ordenador puedes copiar la carpeta completa del proyecto.

## Importar nuevos exámenes

En el menú lateral:

`Importar JSON`

Formato mínimo:

```json
{
  "module_code": "PIA",
  "unit_code": "UT2",
  "title": "Examen 01 · UT2",
  "description": "Descripción opcional",
  "questions": [
    {
      "text": "Texto de la pregunta",
      "options": {
        "A": "Opción A",
        "B": "Opción B",
        "C": "Opción C",
        "D": "Opción D"
      },
      "correct": "B",
      "explanation": "Explicación opcional"
    }
  ]
}
```

## Parar el servidor

Si se inició desde terminal, pulsa:

`Ctrl + C`
