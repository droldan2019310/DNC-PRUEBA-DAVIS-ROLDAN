
### REFLEXION.md
Documento personal donde explicas:

```markdown
# Reflexión Personal
La prueba estuvo interesante, me tomé el timepo de revisar el framework y la base de datos para entender a detalle la arquitectura y esto me ayudó a utilizar los campos y las funciones correctas, me parece interesante la forma en que está estructurado el proyecto y la forma en que se manejan las consultas ya que si es muy parecido con lo que trabajo actualmente.

## 1. Enfoque de Análisis
¿Cómo abordaste cada problema? ¿Qué pensaste primero?
Primero es analizar  el problema y entender exactamente la falla , posteriormente hice casos de ejemplo y luego aplique los conocimientos optimización , temas de redimiento y verificar con la experiencia que tengo en el manejo de este tipo de sistemas, ya que las consultas con una O cuadrática no son recomendables y son bastante frecuentes en softwares legacy.

## 2. Dificultades Encontradas
¿Qué fue más desafiante? ¿Por qué?
Considero que el tema del bug crítico fue el más desafiante ya que no es solo entender el bug sino también la estructura de como funciona el negocio "lo que debería de pasar" y como se tiene la estructura actualmente de la db para tener la solución correcta y no alterar ningún otro proceso o resultado. 

## 3. Aprendizajes
¿Qué aprendiste del código base del ERP?
Aprendí que es un sistema con una arquitectura bien definida y es bastante parecidos a frameworks como laravel, esto me ayudó a entenderlo rápidamente y a poder aplicar los conocimientos de optimización y rendimiento.


## 4. Decisiones Técnicas
¿Qué trade-offs consideraste? ¿Por qué elegiste X sobre Y?
consideré el hecho de tener las transformaciones normalizadas y esquematizadas en una tabla separada, pero es algo que puede ser un poco más complejo de desarrollar siendo un bug crítico y urgente, por lo que opté por la solución más rápida y eficiente. también consideré las consultas más rápidas con sql 8+ pero si es un sistema legacy posiblemente sea abajo de dicha versión, por lo que opté por la solución más compatible. 

## 5. En Producción Real
Si esto fuera tu código en producción, ¿qué harías diferente?

El bug de corrupción de inventario (invertir salidas por entradas y no aplicar la merma) es un error en un ERP que jamás debió llegar a producción. Implementaría Unit Tests y Feature Tests usando PHPUnit o Pest o haría pruebas de regresión que verifiquen los cálculos matemáticos del prorrateo y que validen el estado de la base de datos antes y después del commit de las transacciones (ACID). además buscaría integrar APMs como New Relic, Datadog o al menos un logger de errores centralizado como Sentry.

otro punto es que extraería definitivamente toda la escritura de SQL nativo de los servicios (la actual QueryOptimizado e inserciones) para encapsularla por completo detrás de Repositories, y dejaría la lógica pura de la transformación (rendimientos y prorrateos) en una clase lógica de negocio Domain Model.


## 6. Preguntas para el Equipo
¿Qué te gustaría saber sobre el proyecto antes de tomar decisiones?

Me gustaría saber si hay alguna documentación adicional del proyecto, o si manejan alguna metodología agil.


## 7. Tiempo Invertido
- Parte 1: 40 min
- Parte 2: 65 min  
- Parte 3: 35 min
- Parte 4: 15 min
- Total: 2 horas y 35 minutos
```

---
