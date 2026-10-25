# FP IA & Big Data · Exam Center

Aplicación local para practicar, guardar y analizar exámenes del FP de Especialización en Inteligencia Artificial y Big Data.

## Incluye

- Dashboard con nota media, aprobados, mejor resultado y rendimiento por asignatura.
- Panel lateral: PIA, MIA, SAA, BDA y SBD → unidad → examen.
- PIA UT1 · Examen 01 ya cargado con 10 preguntas.
- Historial de intentos.
- Revisión de errores.
- Repetir examen completo o solo preguntas falladas.
- Banco de exámenes mediante la estructura de datos.
- Creación manual de exámenes.
- Importación de exámenes JSON.
- SQLite cuando está disponible.
- Fallback automático a JSON si PHP no tiene `pdo_sqlite`.

## Inicio rápido

1. Instala PHP 8.x.
2. Abre una terminal dentro de esta carpeta.
3. Ejecuta:

```bash
php -S localhost:8080
```

4. Abre `http://localhost:8080`

En Windows puedes usar `iniciar.bat`.

Consulta `docs/COMO-EJECUTAR.md` para las instrucciones completas.
