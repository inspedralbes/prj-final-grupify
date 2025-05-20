# =Ú Documentació API Grupify

El backend de Grupify exposa una API RESTful que segueix les convencions HTTP estàndard. Tots els punts finals tenen un prefix `/api` i retornen respostes en format JSON.

## =Ë Índex
- [1. Rutes d'autenticació](#1-rutes-dautenticació)
- [2. Rutes de gestió d'usuaris](#2-rutes-de-gestió-dusuaris)
- [3. Rutes de gestió dels rols](#3-rutes-de-gestió-dels-rols)
- [4. Rutes de gestió de formularis](#4-rutes-de-gestió-de-formularis)
- [5. Rutes de gestió preguntes i respostes](#5-rutes-de-gestió-preguntes-i-respostes-dels-formularis)
- [6. Rutes de gestió sociograma i CESC](#6-rutes-de-gestió-sociograma-i-cesc)
- [7. Rutes de gestió cursos i divisions](#7-rutes-de-gestió-cursos-i-divisions)
- [8. Rutes de gestió de grups](#8-rutes-de-gestió-de-grups)
- [9. Rutes de comentaris](#9-rutes-de-comentaris)
- [10. Rutes de notificació](#10-rutes-de-notificació)
- [11. Rutes de Bitàcola](#11-rutes-de-bitàcola)

---

## 1. Rutes d'autenticació

| Endpoint | Mètode | Descripció | Requereix autenticació |
|:---------|:------:|:-----------|:----------------------:|
| `/api/login` | `POST` | Inici de sessió d'usuari | No L |
| `/api/register` | `POST` | Registre d'usuari | No L |
| `/api/logout` | `POST` | Tancament de sessió de l'usuari | Sí  |
| `/api/user` | `GET` | Obtén un usuari autenticat | Sí  |
| `/api/google-login` | `POST` | Inicia la sessió amb Google | No L |

---

## 2. Rutes de gestió d'usuaris

| Endpoint | Mètode | Descripció | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/users` | `GET` | Llistar tots els usuaris | Sí  | Tots |
| `/api/users` | `POST` | Crear un usuari | Sí  | admin |
| `/api/users/{id}` | `GET` | Obtenir un usuari específic | Sí  | Tots |
| `/api/users/{id}` | `PUT` | Actualitzar un usuari | Sí  | admin |
| `/api/users/{id}` | `DELETE` | Eliminar un usuari | Sí  | admin |
| `/api/users/{id}/role` | `PUT` | Assignar rol a un usuari | Sí  | admin |
| `/api/users/{userId}/assign-course-division` | `POST` | Assignar curs i divisió | Sí  | admin, professor, tutor |
| `/api/users/{id}/courses` | `GET` | Obtenir cursos d'un usuari | Sí  | Tots |
| `/api/get-students` | `GET` | Obtenir llista d'estudiants | Sí  | Tots |
| `/api/get-teachers` | `GET` | Obtenir llista de professors | Sí  | Tots |

---

## 3. Rutes de gestió dels rols

| Endpoint | Mètode | Descripció | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/roles` | `GET` | Llistar tots els rols | No L | Tots |
| `/api/roles/{id}` | `GET` | Obtenir un rol específic | No L | Tots |
| `/api/roles` | `POST` | Crear un rol | Sí  | admin |
| `/api/roles/{id}` | `PUT` | Actualitzar un rol | Sí  | admin |
| `/api/roles/{id}` | `DELETE` | Eliminar un rol | Sí  | admin |

---

## 4. Rutes de gestió de formularis

| Endpoint | Mètode | Descripció | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/forms` | `GET` | Llistar tots els formularis | No L | Tots |
| `/api/forms` | `POST` | Crear un formulari | Sí  | professor, tutor, admin |
| `/api/forms/{id}` | `GET` | Obtenir un formulari específic | No L | Tots |
| `/api/forms/{id}` | `PUT`/`PATCH` | Actualitzar un formulari | Sí  | professor, tutor, admin |
| `/api/forms/{id}` | `DELETE` | Eliminar un formulari | Sí  | professor, tutor, admin |
| `/api/forms-save` | `POST` | Desar formulari amb preguntes | Sí  | professor, tutor, admin |
| `/api/forms/active` | `GET` | Obtenir formularis actius | No L | Tots |
| `/api/forms/assign-to-course-division` | `POST` | Assignar formulari a curs i divisió | Sí  | professor, tutor, admin |
| `/api/form-assignments` | `POST` | Assignar formularis a usuaris | Sí  | professor, tutor, admin |
| `/api/form-assignments/teacher/{teacherId}` | `GET` | Obtenir formularis assignats per un professor | Sí  | Tots |
| `/api/form-assignments/{id}` | `GET` | Obtenir detalls d'un formulari | Sí  | Tots |
| `/api/form-assignments/{id}/update-count` | `POST` | Actualitzar el recompte de respostes | Sí  | Tots |
| `/api/form-assignments/{id}/status` | `PATCH` | Actualitzar estat formulari | Sí  | Tots |
| `/api/forms/{formId}/assignment-status` | `PATCH` | Actualitzar l'estat de l'assignació | Sí  | Tots |
| `/api/forms/{formId}/submit-responses` | `POST` | Enviar respostes del formulari | Sí  | Tots |
| `/api/forms/{formId}/questions-and-answers` | `GET` | Obtenir formulari amb preguntes i respostes | Sí  | Tots |
| `/api/forms/{formId}/users` | `GET` | Obtenir usuaris que han respost | Sí  | Tots |
| `/api/form-user/update-status` | `POST` | Actualitzar estat de resposta d'usuari | Sí  | Tots |
| `/api/check-form-completion/{course_id}/{division_id}/{form_id}` | `GET` | Comprovar estat de finalització | Sí  | Tots |

---

## 5. Rutes de gestió preguntes i respostes dels formularis

| Endpoint | Mètode | Descripció | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/questions` | `GET` | Llistar totes les preguntes | Sí  | Tots |
| `/api/questions` | `POST` | Crear una pregunta | Sí  | professor, tutor, admin |
| `/api/questions/{id}` | `GET` | Obtenir una pregunta específica | Sí  | Tots |
| `/api/questions/{id}` | `PUT` | Actualitzar una pregunta | Sí  | professor, tutor, admin |
| `/api/questions/{id}` | `DELETE` | Eliminar una pregunta | Sí  | professor, tutor, admin |
| `/api/answers` | `GET` | Llistar totes les respostes | Sí  | professor, tutor, orientador, admin |
| `/api/answers` | `POST` | Crear una resposta | Sí  | Tots |
| `/api/answers/{id}` | `GET` | Obtenir una resposta específica | Sí  | professor, tutor, orientador, admin |
| `/api/answers/{id}` | `PUT` | Actualitzar una resposta | Sí  | professor, tutor, admin |
| `/api/answers/{id}` | `DELETE` | Eliminar una resposta | Sí  | professor, tutor, admin |
| `/api/all-responses` | `GET` | Obtenir totes les respostes | Sí  | professor, tutor, admin |
| `/api/questions/{questionId}/average-rating` | `GET` | Obtenir valoració mitjana | No L | Tots |

---

## 6. Rutes de gestió sociograma i CESC

| Endpoint | Mètode | Descripció | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/sociogram-relationships` | `GET` | Obtenir relacions sociograma | Sí  | tutor, admin |
| `/api/sociogram-relationships/user/{id}` | `GET` | Obtenir relacions per usuari | Sí  | tutor, admin |
| `/api/sociogram-relationships` | `POST` | Guardar relacions sociograma | Sí  | Tots |
| `/api/sociogram/responses` | `POST` | Obtenir respostes per curs i divisió | Sí  | tutor, admin |
| `/api/cesc-relationships` | `GET` | Obtenir relacions CESC | Sí  | tutor, admin |
| `/api/cesc-relationships/user/{id}` | `GET` | Obtenir relacions CESC per usuari | Sí  | tutor, admin |
| `/api/cesc-relationships` | `POST` | Guardar relacions CESC | Sí  | Tots |
| `/api/cesc/responses` | `POST` | Obtenir respostes CESC per curs i divisió | Sí  | tutor, admin |
| `/api/cesc/calcular-resultados` | `POST` | Calcular resultats CESC | Sí  | tutor, orientador, admin |
| `/api/cesc/ver-resultados` | `GET` | Veure resultats CESC | Sí  | tutor, orientador, admin |
| `/api/cesc/graficas-tags` | `GET` | Obtenir dades per gràfiques | Sí  | tutor, orientador, admin |

---

## 7. Rutes de gestió cursos i divisions

| Endpoint | Mètode | Descripció | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/courses` | `GET` | Llistar tots els cursos | Sí  | Tots |
| `/api/courses` | `POST` | Crear un curs | Sí  | admin |
| `/api/courses/{id}` | `GET` | Obtenir un curs específic | Sí  | Tots |
| `/api/courses/{id}` | `PUT` | Actualitzar un curs | Sí  | admin |
| `/api/courses/{id}` | `DELETE` | Eliminar un curs | Sí  | admin |
| `/api/divisions` | `GET` | Llistar totes les divisions | Sí  | Tots |
| `/api/divisions` | `POST` | Crear una divisió | Sí  | admin |
| `/api/divisions/{id}` | `GET` | Obtenir una divisió específica | Sí  | Tots |
| `/api/divisions/{id}` | `PUT` | Actualitzar una divisió | Sí  | admin |
| `/api/divisions/{id}` | `DELETE` | Eliminar una divisió | Sí  | admin |
| `/api/course-divisions` | `GET` | Obtenir divisions per curs | Sí  | Tots |
| `/api/courses-with-divisions` | `GET` | Obtenir cursos amb divisions | Sí  | Tots |
| `/api/course-division-user` | `POST` | Assignar usuaris a cursos i divisions | Sí  | admin |

---

## 8. Rutes de gestió de grups

| Endpoint | Mètode | Descripció | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/groups` | `GET` | Obtenir tots els grups | Sí  | - |
| `/api/groups/{id}` | `GET` | Obtenir un grup específic | Sí  | - |
| `/api/groups` | `POST` | Crear un nou grup | Sí  | - |
| `/api/groups/{id}` | `PUT` | Actualitzar un grup existent | Sí  | - |
| `/api/groups/{id}` | `DELETE` | Eliminar un grup | Sí  | - |
| `/api/groups/{id}/members` | `GET` | Obtenir els membres d'un grup | Sí  | - |
| `/api/groups/{id}/addStudentsToGroup` | `POST` | Afegir estudiants a un grup | Sí  | - |
| `/api/groups/{groupId}/removeStudentFromGroup` | `DELETE` | Eliminar un estudiant d'un grup | Sí  | - |

---

## 9. Rutes de comentaris

| Endpoint | Mètode | Descripció | Requereix auth |
|:---------|:------:|:-----------|:-------------:|
| `/api/comments` | `GET` | Obtenir tots els comentaris | Sí  |
| `/api/comments` | `POST` | Crear un comentari | Sí  |
| `/api/comments/{id}` | `GET` | Obtenir un comentari específic | Sí  |
| `/api/comments/{id}` | `PUT` | Actualitzar un comentari | Sí  |
| `/api/comments/{id}` | `DELETE` | Eliminar un comentari | Sí  |
| `/api/comments/students/{studentId}` | `GET` | Obtenir comentaris d'un estudiant | Sí  |
| `/api/comments/teachers/{teacherId}` | `GET` | Obtenir comentaris fets per un professor | Sí  |
| `/api/groups/{idGroup}/comments` | `GET` | Obtenir comentaris d'un grup | Sí  |
| `/api/groups/{idGroup}/comments` | `POST` | Afegir comentari a un grup | Sí  |
| `/api/groups/{idGroup}/comments/{commentId}` | `PUT` | Actualitzar comentari d'un grup | Sí  |
| `/api/groups/{idGroup}/comments/{commentId}` | `DELETE` | Eliminar comentari d'un grup | Sí  |

---

## 10. Rutes de notificació

| Endpoint | Mètode | Descripció | Requereix auth |
|:---------|:------:|:-----------|:-------------:|
| `/api/notifications` | `GET` | Obtenir totes les notificacions | Sí  |
| `/api/notifications` | `POST` | Crear una notificació | Sí  |
| `/api/teacher-notifications` | `GET` | Obtenir notificacions per a professors | Sí  |
| `/api/notifications/{id}` | `DELETE` | Eliminar una notificació | Sí  |

---

## 11. Rutes de Bitàcola

| Endpoint | Mètode | Descripció | Requereix auth |
|:---------|:------:|:-----------|:-------------:|
| `/api/bitacoras` | `GET` | Obtenir totes les bitàcoles | Sí  |
| `/api/bitacoras` | `POST` | Crear una bitàcola | Sí  |
| `/api/bitacoras/{id}` | `GET` | Obtenir una bitàcola específica | Sí  |
| `/api/bitacoras/{id}` | `PUT` | Actualitzar una bitàcola | Sí  |
| `/api/bitacoras/{id}` | `DELETE` | Eliminar una bitàcola | Sí  |
| `/api/bitacoras/{bitacoraId}/notes` | `GET` | Obtenir notes d'una bitàcola | Sí  |
| `/api/bitacoras/{bitacoraId}/notes` | `POST` | Afegir nota a una bitàcola | Sí  |
| `/api/bitacoras/{bitacoraId}/notes/{noteId}` | `GET` | Obtenir una nota específica | Sí  |
| `/api/bitacoras/{bitacoraId}/notes/{noteId}` | `PUT` | Actualitzar una nota | Sí  |
| `/api/bitacoras/{bitacoraId}/notes/{noteId}` | `DELETE` | Eliminar una nota | Sí  |
| `/api/bitacoras/{bitacoraId}/user/{userId}/notes` | `GET` | Obtenir notes d'un usuari | Sí  |
| `/api/bitacoras/{groupId}/notes` | `GET` | Obtenir notes d'un grup | Sí  |