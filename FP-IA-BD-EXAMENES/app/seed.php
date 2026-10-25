<?php
declare(strict_types=1);

function seed_catalog(): array {
    return [
        'modules' => [
            ['code'=>'PIA','name'=>'Programación de Inteligencia Artificial','block'=>'IA'],
            ['code'=>'MIA','name'=>'Modelos de Inteligencia Artificial','block'=>'IA'],
            ['code'=>'SAA','name'=>'Sistemas de Aprendizaje Automático','block'=>'IA'],
            ['code'=>'BDA','name'=>'Big Data Aplicado','block'=>'Datos'],
            ['code'=>'SBD','name'=>'Sistemas de Big Data','block'=>'Datos'],
        ],
        'exams' => [
            [
                'id' => 1,
                'module_code' => 'PIA',
                'unit_code' => 'UT1',
                'title' => 'Examen 01 · Introducción a la programación de IA',
                'description' => 'Examen de práctica reconstruido a partir del primer intento en Moodle.',
                'created_at' => '2026-10-04T12:00:00+02:00',
                'questions' => [
                    [
                        'id'=>1,
                        'text'=>'Durante el desarrollo de una aplicación basada en IA, el estudio estadístico de los datos y los valores perdidos se realiza durante...',
                        'options'=>[
                            'A'=>'La preparación de los datos, en la fase de ingesta de datos.',
                            'B'=>'La preparación de los datos, en la fase de análisis de datos.',
                            'C'=>'El ajuste del modelo.',
                            'D'=>'La monitorización del sistema.'
                        ],
                        'correct'=>'B',
                        'explanation'=>'El análisis estadístico y los valores perdidos se estudian en la fase de análisis dentro de la preparación de datos.'
                    ],
                    [
                        'id'=>2,
                        'text'=>'¿Cuál de las siguientes afirmaciones sobre la Ley de IA de la UE es cierta?',
                        'options'=>[
                            'A'=>'Impone unas estrictas obligaciones a las aplicaciones catalogadas como riesgo limitado.',
                            'B'=>'Prohíbe la comercialización de sistemas catalogados como alto riesgo.',
                            'C'=>'Garantiza que los sistemas de IA respeten los derechos fundamentales.',
                            'D'=>'Establece cinco niveles de riesgo en los que se clasifican los sistemas basados en IA.'
                        ],
                        'correct'=>'C',
                        'explanation'=>'En este examen, la opción correcta es que la Ley de IA busca garantizar el respeto de los derechos fundamentales.'
                    ],
                    [
                        'id'=>3,
                        'text'=>'La fase del diseño del software en la que se determinan las necesidades de los usuarios finales y se definen las funcionalidades y características que debe tener el software, es la fase de:',
                        'options'=>[
                            'A'=>'Análisis de Requisitos.',
                            'B'=>'Codificación.',
                            'C'=>'Planificación del Desarrollo.',
                            'D'=>'Despliegue.'
                        ],
                        'correct'=>'A',
                        'explanation'=>'El análisis de requisitos determina las necesidades, funcionalidades y características que deberá tener el software.'
                    ],
                    [
                        'id'=>4,
                        'text'=>'Es un lenguaje muy utilizado en el ámbito de la estadística y el análisis de datos, lo que lo hace ideal para la investigación en ciencia de datos y aprendizaje automático. Tiene una amplia gama de paquetes y librerías especializadas en análisis estadístico y visualización de datos.',
                        'options'=>[
                            'A'=>'Java',
                            'B'=>'R',
                            'C'=>'Python',
                            'D'=>'C++'
                        ],
                        'correct'=>'B',
                        'explanation'=>'La descripción del examen corresponde a R, especialmente orientado a estadística, análisis y visualización de datos.'
                    ],
                    [
                        'id'=>5,
                        'text'=>'Los lenguajes que están más cerca del lenguaje máquina y son altamente dependientes del hardware donde se ejecutará el programa se consideran...',
                        'options'=>[
                            'A'=>'Lenguajes de Bajo Nivel',
                            'B'=>'Lenguajes de Alto Nivel',
                            'C'=>'Lenguajes Compilados',
                            'D'=>'Lenguajes Interpretados'
                        ],
                        'correct'=>'A',
                        'explanation'=>'Los lenguajes de bajo nivel están próximos al lenguaje máquina y dependen más del hardware.'
                    ],
                    [
                        'id'=>6,
                        'text'=>'Por sus características Java es considerado un lenguaje...',
                        'options'=>[
                            'A'=>'Compilado.',
                            'B'=>'Interpretado.',
                            'C'=>'Híbrido.',
                            'D'=>'Compuesto.'
                        ],
                        'correct'=>'C',
                        'explanation'=>'En el temario del examen, Java se clasifica como híbrido: compila a bytecode y se ejecuta sobre la JVM.'
                    ],
                    [
                        'id'=>7,
                        'text'=>'¿A qué término corresponde la definición “es una secuencia lógica de instrucciones diseñada para resolver un problema de manera efectiva, eficiente y precisa, utilizando los recursos computacionales disponibles”?',
                        'options'=>[
                            'A'=>'Software.',
                            'B'=>'Código.',
                            'C'=>'Programa.',
                            'D'=>'Algoritmo.'
                        ],
                        'correct'=>'D',
                        'explanation'=>'Una secuencia lógica de instrucciones diseñada para resolver un problema es un algoritmo.'
                    ],
                    [
                        'id'=>8,
                        'text'=>'Si nos encargan desarrollar una aplicación que identifique transacciones fraudulentas a partir de sus características, estamos frente a un problema de...',
                        'options'=>[
                            'A'=>'Procesamiento del Lenguaje Natural.',
                            'B'=>'Recomendación Personalizada.',
                            'C'=>'Clasificación y Reconocimiento de Patrones.',
                            'D'=>'Generación de Contenidos.'
                        ],
                        'correct'=>'C',
                        'explanation'=>'Distinguir transacciones fraudulentas según sus características es un problema de clasificación y reconocimiento de patrones.'
                    ],
                    [
                        'id'=>9,
                        'text'=>'Una aplicación basada en Inteligencia Artificial que clasifica a los aspirantes a un puesto de trabajo y realiza la selección de personal basándose en su CV y su comportamiento en redes sociales, sería clasificada por la ley de IA de la UE como:',
                        'options'=>[
                            'A'=>'Riesgo Inaceptable.',
                            'B'=>'Alto Riesgo.',
                            'C'=>'Riesgo Limitado.',
                            'D'=>'Riesgo Mínimo.'
                        ],
                        'correct'=>'B',
                        'explanation'=>'La selección de personal mediante IA se encuadra como alto riesgo en el examen.'
                    ],
                    [
                        'id'=>10,
                        'text'=>'Si programando en un lenguaje de programación determinado podemos realizar operaciones que implican variables de distinto tipo sin necesidad de realizar conversiones explícitas, se trata de un lenguaje de:',
                        'options'=>[
                            'A'=>'Tipado Estático.',
                            'B'=>'Tipado Dinámico.',
                            'C'=>'Tipado Débil.',
                            'D'=>'Tipado Fuerte.'
                        ],
                        'correct'=>'C',
                        'explanation'=>'La respuesta confirmada en Moodle fue Tipado Débil.'
                    ],
                ]
            ]
        ],
        'attempts' => [
            [
                'id'=>1,
                'exam_id'=>1,
                'started_at'=>'2026-10-04T12:10:00+02:00',
                'finished_at'=>'2026-10-04T12:17:00+02:00',
                'duration_seconds'=>420,
                'score'=>7.0,
                'correct_count'=>7,
                'total_count'=>10,
                'answers'=>[
                    1=>'B',2=>'D',3=>'A',4=>'C',5=>'A',6=>'A',7=>'D',8=>'C',9=>'B',10=>'C'
                ]
            ]
        ]
    ];
}
